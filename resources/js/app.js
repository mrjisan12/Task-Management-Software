import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Alpine = Alpine;
window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    },
});

Alpine.store('notifications', {
    items: [],
    unread: Number(document.body.dataset.unreadNotifications ?? 0),
    soundsEnabled: document.body.dataset.notificationSounds !== '0',
    volume: Number(document.body.dataset.notificationVolume ?? 50),
    audioUnlocked: false,
    lastSoundAt: 0,
    seen: {},
    timers: {},

    init() {
        const unlock = () => {
            this.audioUnlocked = true;
            window.removeEventListener('click', unlock);
            window.removeEventListener('keydown', unlock);
        };

        window.addEventListener('click', unlock, { once: true });
        window.addEventListener('keydown', unlock, { once: true });
    },

    push(notification) {
        const payload = notification?.data ?? notification;
        const key = notification?.id
            ?? [payload.event, payload.task_id, payload.title, payload.body].filter(Boolean).join(':');

        if (key && this.seen[key]) {
            return;
        }

        if (key) {
            this.seen[key] = Date.now();
        }

        this.unread += 1;
        const itemId = key || crypto.randomUUID();

        this.items.unshift({
            id: itemId,
            title: payload.title ?? 'Notification',
            body: payload.body ?? '',
            action_url: payload.action_url ?? null,
            sound: payload.sound ?? 'default',
        });

        clearTimeout(this.timers[itemId]);
        this.timers[itemId] = setTimeout(() => {
            this.dismiss(itemId);
        }, 5000);

        const cutoff = Date.now() - 60000;
        Object.keys(this.seen).forEach((seenKey) => {
            if (this.seen[seenKey] < cutoff) {
                delete this.seen[seenKey];
            }
        });

        this.items = this.items.slice(0, 5);
        this.play(payload.sound ?? 'default');
    },

    dismiss(id) {
        this.items = this.items.filter((item) => item.id !== id);

        if (this.timers[id]) {
            clearTimeout(this.timers[id]);
            delete this.timers[id];
        }
    },

    play(sound) {
        if (! this.soundsEnabled || ! this.audioUnlocked) {
            return;
        }

        const now = Date.now();

        if (now - this.lastSoundAt < 800) {
            return;
        }

        this.lastSoundAt = now;

        const audio = new Audio(`/sounds/${sound}.wav`);
        audio.volume = Math.max(0, Math.min(0.35, (this.volume / 100) * 0.35));
        audio.play().catch(() => {});
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.store('notifications').init();
});

const showButtonLoading = (button) => {
    if (! button || button.classList.contains('loading') || button.dataset.noLoading === 'true') {
        return;
    }

    button.classList.add('loading');
    button.setAttribute('aria-busy', 'true');

    if (button.tagName === 'BUTTON') {
        button.disabled = true;
    }

    const spinner = document.createElement('span');
    spinner.className = 'button-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    button.appendChild(spinner);
};

document.addEventListener('submit', (event) => {
    const button = event.submitter?.classList?.contains('button')
        ? event.submitter
        : event.target.querySelector('button.button[type="submit"]');

    showButtonLoading(button);
}, true);

document.addEventListener('click', (event) => {
    const link = event.target.closest('a.button');

    if (! link || link.target === '_blank' || link.getAttribute('href') === '#') {
        return;
    }

    showButtonLoading(link);
}, true);

const userId = document.body.dataset.userId;
const companyId = document.body.dataset.companyId;
const teamIds = (document.body.dataset.teamIds ?? '')
    .split(',')
    .map((id) => id.trim())
    .filter(Boolean);

const dispatchTaskAssigned = (event) => {
    if (! event?.task?.id) {
        return;
    }

    window.dispatchEvent(new CustomEvent('task-assigned', {
        detail: event.task,
    }));
};

const dispatchTaskCompleted = (event) => {
    if (! event?.task?.id) {
        return;
    }

    window.dispatchEvent(new CustomEvent('task-completed', {
        detail: event.task,
    }));
};

if (userId) {
    window.Echo.private(`user.${userId}`)
        .notification((notification) => {
            Alpine.store('notifications').push(notification);
        })
        .listen('.task.assigned', dispatchTaskAssigned)
        .listen('.task.completed', dispatchTaskCompleted);
}

teamIds.forEach((teamId) => {
    window.Echo.private(`team.${teamId}`)
        .listen('.task.assigned', dispatchTaskAssigned)
        .listen('.task.completed', dispatchTaskCompleted);
});

if (companyId) {
    window.Echo.private(`company.${companyId}`)
        .listen('.task.assigned', () => window.dispatchEvent(new CustomEvent('task-feed-refresh')))
        .listen('.task.completed', (event) => {
            dispatchTaskCompleted(event);
            window.dispatchEvent(new CustomEvent('task-feed-refresh'));
        });
}

Alpine.start();

