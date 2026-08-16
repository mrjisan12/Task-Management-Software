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

    $formatSize = function ($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    };

    $images = $task->attachments
        ->filter(fn ($attachment) => str_starts_with((string) $attachment->mime_type, 'image/'))
        ->values();

    $files = $task->attachments
        ->reject(fn ($attachment) => str_starts_with((string) $attachment->mime_type, 'image/'))
        ->values();

    $imagePayload = $images->map(fn ($attachment) => [
        'id' => $attachment->id,
        'name' => $attachment->original_name,
        'url' => route('task-attachments.view', $attachment),
        'download_url' => route('task-attachments.download', $attachment),
    ])->values();
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

        @if ($task->attachments->isNotEmpty())
            <section
                class="panel span-12 attachment-panel"
                x-data="{
                    images: @js($imagePayload),
                    index: -1,
                    zoom: 1,
                    get current() {
                        return this.index >= 0 ? (this.images[this.index] || null) : null;
                    },
                    openImage(position) {
                        this.index = position;
                        this.zoom = 1;
                    },
                    close() {
                        this.index = -1;
                        this.zoom = 1;
                    },
                    next() {
                        if (this.images.length < 1) return;
                        this.index = (this.index + 1) % this.images.length;
                        this.zoom = 1;
                    },
                    prev() {
                        if (this.images.length < 1) return;
                        this.index = (this.index - 1 + this.images.length) % this.images.length;
                        this.zoom = 1;
                    },
                    zoomIn() {
                        this.zoom = Math.min(3, Number((this.zoom + 0.25).toFixed(2)));
                    },
                    zoomOut() {
                        this.zoom = Math.max(1, Number((this.zoom - 0.25).toFixed(2)));
                    },
                }"
                x-on:keydown.escape.window="close()"
                x-on:keydown.arrow-right.window="if (current) next()"
                x-on:keydown.arrow-left.window="if (current) prev()"
            >
                <div class="section-head">
                    <div>
                        <h2 class="section-title">Attachments</h2>
                        <p class="subtitle">{{ $task->attachments->count() }} file{{ $task->attachments->count() === 1 ? '' : 's' }}</p>
                    </div>
                </div>

                @if ($images->isNotEmpty())
                    <div class="attachment-gallery">
                        @foreach ($images as $image)
                            <button class="attachment-thumb" type="button" x-on:click="openImage({{ $loop->index }})">
                                <img src="{{ route('task-attachments.view', $image) }}" alt="{{ $image->original_name }}">
                                <span>{{ $loop->iteration }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if ($files->isNotEmpty())
                    <div class="attachment-files">
                        @foreach ($files as $file)
                            <a class="attachment-file" href="{{ route('task-attachments.download', $file) }}">
                                <span class="attachment-file-icon">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                                <span>
                                    <strong>{{ $file->original_name }}</strong>
                                    <small>{{ $file->mime_type ?: 'File' }} &middot; {{ $formatSize($file->size) }}</small>
                                </span>
                                <em>Download</em>
                            </a>
                        @endforeach
                    </div>
                @endif

                <template x-if="current">
                    <div class="image-lightbox" x-on:click.self="close()" x-cloak>
                        <div class="image-lightbox-toolbar">
                            <button type="button" x-on:click="prev()" x-bind:disabled="images.length < 2">Prev</button>
                            <button type="button" x-on:click="zoomOut()" x-bind:disabled="zoom <= 1">-</button>
                            <span x-text="Math.round(zoom * 100) + '%'"></span>
                            <button type="button" x-on:click="zoomIn()" x-bind:disabled="zoom >= 3">+</button>
                            <button type="button" x-on:click="next()" x-bind:disabled="images.length < 2">Next</button>
                            <a x-bind:href="current.download_url">Download</a>
                            <button type="button" x-on:click="close()">Close</button>
                        </div>

                        <div class="image-lightbox-stage">
                            <img
                                x-bind:src="current.url"
                                x-bind:alt="current.name"
                                x-bind:style="'transform: scale(' + zoom + ')'"
                            >
                        </div>
                    </div>
                </template>
            </section>
        @endif

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
