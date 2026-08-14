@php
    $formatComment = fn ($comment) => [
        'id' => $comment->id,
        'task_id' => $comment->task_id,
        'body' => $comment->body,
        'user_id' => $comment->user_id,
        'user_name' => $comment->user?->name,
        'user_photo' => $comment->user?->profile?->photoUrl(),
        'user_initial' => str($comment->user?->name ?? 'U')->substr(0, 1)->upper()->toString(),
        'created_at' => $comment->created_at?->format('M j, g:i A'),
        'created_at_human' => $comment->created_at?->diffForHumans(),
    ];
@endphp

<x-layouts.app>
    <div class="grid">
        <section class="panel span-8">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div>
                    <h1 class="title">{{ $task->title }}</h1>
                    <p class="subtitle">{{ $task->company->name }} · created by {{ $task->creator->name }}</p>
                </div>
                <span class="badge">{{ $task->status->name }}</span>
            </div>

            <div class="list">
                <div class="row">
                    <span class="muted">Priority</span>
                    <strong>{{ $task->priority->name }}</strong>
                </div>
                <div class="row">
                    <span class="muted">Due</span>
                    <strong>{{ $task->due_at ? $task->due_at->format('M j, Y g:i A') : 'No due date' }}</strong>
                </div>
                <div class="row">
                    <span class="muted">Team</span>
                    <strong>{{ $task->team?->name ?: 'No team scope' }}</strong>
                </div>
            </div>

            @if ($task->description)
                <div class="field">
                    <label class="label">Description</label>
                    <p style="white-space: pre-wrap;">{{ $task->description }}</p>
                </div>
            @endif
        </section>

        <section class="panel span-4">
            <h2 class="title" style="font-size: 20px;">Assignees</h2>
            <div class="list">
                @foreach ($task->assignments as $assignment)
                    <div class="row">
                        <div>
                            <strong>{{ $assignment->assignee?->name ?: $assignment->team?->name }}</strong>
                            <div class="muted">{{ str($assignment->status)->headline() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @can('complete', $task)
                <form method="POST" action="{{ route('tasks.complete', $task) }}" class="field">
                    @csrf
                    <label class="label" for="completion_comment">Completion Comment</label>
                    <textarea class="input" id="completion_comment" name="completion_comment" style="height: 110px; padding-top: 10px;" placeholder="Optional note"></textarea>
                    @error('completion_comment') <div class="error">{{ $message }}</div> @enderror
                    <div class="field">
                        <button class="button" type="submit">Complete Task</button>
                    </div>
                </form>
            @endcan
        </section>

        <section
            class="panel span-12 activity-panel"
            x-data="{
                taskId: {{ $task->id }},
                currentUserId: {{ auth()->id() }},
                comments: @js($task->comments->map($formatComment)->values()),
                body: '',
                sending: false,
                error: '',
                addComment(comment) {
                    if (! comment?.id || Number(comment.task_id) !== Number(this.taskId) || this.comments.some((item) => item.id === comment.id)) {
                        return;
                    }

                    this.comments.push(comment);
                    this.$nextTick(() => this.scrollToEnd());
                },
                async submitComment() {
                    const message = this.body.trim();

                    if (! message || this.sending) {
                        return;
                    }

                    this.sending = true;
                    this.error = '';

                    try {
                        const response = await fetch('{{ route('tasks.comments.store', $task) }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ body: message }),
                        });

                        const payload = await response.json();

                        if (! response.ok) {
                            this.error = payload.message || 'Could not add comment.';
                            return;
                        }

                        this.body = '';
                        this.addComment(payload.comment);
                    } catch (error) {
                        this.error = 'Could not add comment.';
                    } finally {
                        this.sending = false;
                    }
                },
                scrollToEnd() {
                    const feed = this.$refs.feed;
                    if (feed) {
                        feed.scrollTop = feed.scrollHeight;
                    }
                },
                init() {
                    this.$nextTick(() => this.scrollToEnd());
                }
            }"
            x-on:task-commented.window="addComment($event.detail)"
        >
            <div class="section-head">
                <div>
                    <h2 class="section-title">Activity</h2>
                    <p class="subtitle">Task discussion and doubts.</p>
                </div>
            </div>

            <div class="comment-feed" x-ref="feed">
                <template x-for="comment in comments" :key="comment.id">
                    <div class="comment-row" x-bind:class="{ mine: Number(comment.user_id) === Number(currentUserId) }">
                        <span class="avatar sm">
                            <template x-if="comment.user_photo">
                                <img :src="comment.user_photo" :alt="comment.user_name || 'User'">
                            </template>
                            <template x-if="! comment.user_photo">
                                <span x-text="comment.user_initial || 'U'"></span>
                            </template>
                        </span>
                        <div class="comment-bubble">
                            <div class="comment-meta">
                                <strong x-text="comment.user_name || 'User'"></strong>
                                <span x-text="comment.created_at_human || comment.created_at"></span>
                            </div>
                            <p x-text="comment.body"></p>
                        </div>
                    </div>
                </template>

                <p class="subtitle" x-show="comments.length === 0">No comments yet.</p>
            </div>

            <form class="comment-form" x-on:submit.prevent="submitComment">
                <textarea class="input" x-model="body" rows="3" placeholder="Ask a question or reply..." maxlength="2000"></textarea>
                <div class="comment-actions">
                    <p class="error" x-show="error" x-text="error"></p>
                    <button class="button" type="submit" data-no-loading="true" x-bind:disabled="sending || ! body.trim()">
                        <span x-text="sending ? 'Sending...' : 'Send Comment'"></span>
                        <span class="button-spinner" x-show="sending" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
