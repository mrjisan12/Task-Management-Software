@php
    $formatTask = fn ($task) => [
        'id' => $task->id,
        'title' => $task->title,
        'creator' => $task->creator?->name,
        'creator_photo' => $task->creator?->profile?->photoUrl(),
        'creator_initial' => str($task->creator?->name ?? 'U')->substr(0, 1)->upper()->toString(),
        'due_at' => $task->due_at?->format('M j, g:i A'),
        'due_at_ts' => $task->due_at?->getTimestampMs(),
        'priority' => $task->priority?->name,
        'status' => $task->status?->name,
        'url' => route('tasks.show', $task),
    ];
@endphp

<x-layouts.app>
    <div class="grid">
        <section
            class="panel span-12"
            x-data="{
                tab: 'pending',
                now: Date.now(),
                pending: @js($pendingTasks->map($formatTask)->values()),
                completed: @js($completedTasks->map($formatTask)->values()),
                init() {
                    setInterval(() => this.now = Date.now(), 1000);
                },
                addPending(task) {
                    if (! task?.id || this.pending.some((item) => item.id === task.id) || this.completed.some((item) => item.id === task.id)) {
                        return;
                    }

                    this.pending.push({ ...task, status: task.status || 'Pending' });
                },
                markCompleted(task) {
                    if (! task?.id) {
                        return;
                    }

                    const existing = this.pending.find((item) => item.id === task.id) || this.completed.find((item) => item.id === task.id) || task;
                    this.pending = this.pending.filter((item) => item.id !== task.id);

                    if (! this.completed.some((item) => item.id === task.id)) {
                        this.completed.unshift({ ...existing, ...task, status: 'Completed' });
                    }
                },
                priorityClass(priority) {
                    const value = (priority || '').toLowerCase();
                    return {
                        danger: value === 'critical',
                        warning: value === 'high',
                        neutral: value === 'low'
                    };
                },
                isDue(task) {
                    return task.due_at_ts && Number(task.due_at_ts) <= this.now;
                },
                countdown(task) {
                    if (! task.due_at_ts) {
                        return '';
                    }

                    const remaining = Math.max(0, Number(task.due_at_ts) - this.now);

                    if (remaining <= 0) {
                        return 'Due';
                    }

                    const totalSeconds = Math.floor(remaining / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    if (days > 0) {
                        return `${days}d ${hours}h left`;
                    }

                    if (hours > 0) {
                        return `${hours}h ${minutes}m left`;
                    }

                    return `${minutes}m ${seconds}s left`;
                }
            }"
            x-on:task-assigned.window="addPending($event.detail)"
            x-on:task-completed.window="markCompleted($event.detail)"
        >
            <div class="section-head">
                <div>
                    <h1 class="title">Tasks</h1>
                    <p class="subtitle">{{ $company->name }} assigned work.</p>
                </div>
                <a class="button" href="{{ route('tasks.create') }}">Create Task</a>
            </div>

            <div class="section-head">
                <div class="tabs" role="tablist" aria-label="Task status tabs">
                    <button class="tab-button" type="button" x-bind:class="{ active: tab === 'pending' }" x-on:click="tab = 'pending'">
                        Pending <span x-text="'(' + pending.length + ')'"></span>
                    </button>
                    <button class="tab-button" type="button" x-bind:class="{ active: tab === 'completed' }" x-on:click="tab = 'completed'">
                        Completed <span x-text="'(' + completed.length + ')'"></span>
                    </button>
                </div>
            </div>

            <div class="task-list" x-show="tab === 'pending'">
                <template x-for="task in pending" :key="task.id">
                    <a class="task-card" :href="task.url">
                        <div class="task-card-main">
                            <span class="avatar sm">
                                <template x-if="task.creator_photo">
                                    <img :src="task.creator_photo" :alt="task.creator || 'Assigner'">
                                </template>
                                <template x-if="! task.creator_photo">
                                    <span x-text="task.creator_initial || 'U'"></span>
                                </template>
                            </span>
                            <div>
                                <strong class="task-card-title" x-text="task.title"></strong>
                                <div class="task-card-meta">
                                    <span x-text="(task.creator || 'Someone') + ' assigned this'"></span>
                                    <template x-if="task.due_at">
                                        <span x-text="'Due ' + task.due_at"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="task-card-badges">
                            <template x-if="task.due_at_ts">
                                <span class="timer-pill" x-bind:class="{ due: isDue(task) }" x-text="countdown(task)"></span>
                            </template>
                            <span class="badge" x-bind:class="priorityClass(task.priority)" x-text="task.priority || 'Priority'"></span>
                            <span class="badge warning" x-text="task.status || 'Pending'"></span>
                        </div>
                    </a>
                </template>

                <p class="subtitle" x-show="pending.length === 0">No pending tasks right now.</p>
            </div>

            <div class="task-list" x-show="tab === 'completed'" x-cloak>
                <template x-for="task in completed" :key="task.id">
                    <a class="task-card" :href="task.url">
                        <div class="task-card-main">
                            <span class="avatar sm">
                                <template x-if="task.creator_photo">
                                    <img :src="task.creator_photo" :alt="task.creator || 'Assigner'">
                                </template>
                                <template x-if="! task.creator_photo">
                                    <span x-text="task.creator_initial || 'U'"></span>
                                </template>
                            </span>
                            <div>
                                <strong class="task-card-title" x-text="task.title"></strong>
                                <div class="task-card-meta">
                                    <span x-text="(task.creator || 'Someone') + ' assigned this'"></span>
                                    <template x-if="task.due_at">
                                        <span x-text="'Due ' + task.due_at"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="task-card-badges">
                            <span class="badge" x-bind:class="priorityClass(task.priority)" x-text="task.priority || 'Priority'"></span>
                            <span class="badge success">Completed</span>
                        </div>
                    </a>
                </template>

                <p class="subtitle" x-show="completed.length === 0">No completed tasks yet.</p>
            </div>
        </section>
    </div>
</x-layouts.app>
