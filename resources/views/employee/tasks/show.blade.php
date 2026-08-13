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

        <section class="panel span-12">
            <h2 class="title" style="font-size: 20px;">Activity</h2>
            <div class="list">
                @forelse ($task->comments as $comment)
                    <div class="row">
                        <div>
                            <strong>{{ $comment->user->name }}</strong>
                            <div class="muted">{{ $comment->created_at->format('M j, g:i A') }}</div>
                            <p>{{ $comment->body }}</p>
                        </div>
                    </div>
                @empty
                    <p class="subtitle">No comments yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
