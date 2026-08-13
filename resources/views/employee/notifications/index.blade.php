<x-layouts.app>
    <div class="grid">
        <section class="panel span-8">
            <div style="display: flex; justify-content: space-between; gap: 12px; align-items: center;">
                <div>
                    <h1 class="title">Notifications</h1>
                    <p class="subtitle">Realtime updates and task activity.</p>
                </div>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="button secondary" type="submit">Mark All Read</button>
                </form>
            </div>

            <div class="list">
                @forelse ($notifications as $notification)
                    @php($data = $notification->data)
                    <a class="row notification-row {{ $notification->read_at ? 'read' : 'unread' }}" href="{{ $data['action_url'] ?? '#' }}">
                        <div>
                            <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                            <div class="muted">{{ $data['body'] ?? '' }}</div>
                            <div class="muted">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="badge {{ $notification->read_at ? 'neutral' : 'success' }}">{{ $notification->read_at ? 'Read' : 'New' }}</span>
                    </a>
                @empty
                    <p class="subtitle">No notifications yet.</p>
                @endforelse
            </div>

            <div style="margin-top: 16px;">
                {{ $notifications->links() }}
            </div>
        </section>

        <section class="panel span-4">
            <h2 class="title" style="font-size: 20px;">Sound Settings</h2>
            <form method="POST" action="{{ route('notifications.settings') }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label>
                        <input type="checkbox" name="sounds_enabled" value="1" @checked($setting->sounds_enabled)>
                        <span>Notification sounds</span>
                    </label>
                </div>

                <div class="field" x-data="{ volume: {{ (int) old('sound_volume', $setting->sound_volume) }} }">
                    <label class="label" for="sound_volume">Volume</label>
                    <div class="range-control">
                        <div class="range-row">
                            <input
                                class="range-input"
                                id="sound_volume"
                                name="sound_volume"
                                type="range"
                                min="0"
                                max="100"
                                step="1"
                                x-model="volume"
                            >
                            <span class="range-value" x-text="volume + '%'"></span>
                        </div>
                    </div>
                    @error('sound_volume') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <button class="button" type="submit">Save Settings</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
