@php
    $assigneeUserId = old('assignee_user_id', $task->assignments->firstWhere('assignee_user_id')?->assignee_user_id);
    $assigneeTeamId = old('assignee_team_id', $task->assignments->firstWhere('assignee_team_id')?->assignee_team_id);
@endphp

<x-layouts.app>
    <div class="grid">
        <section class="panel span-8">
            <h1 class="title">Edit Task</h1>
            <p class="subtitle">{{ $company->name }}</p>

            <form method="POST" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label class="label" for="title">Title</label>
                    <input class="input @error('title') error-input @enderror" id="title" name="title" value="{{ old('title', $task->title) }}" required>
                    @error('title') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="label" for="description">Description</label>
                    <textarea class="input @error('description') error-input @enderror" id="description" name="description" style="height: 120px; padding-top: 10px;">{{ old('description', $task->description) }}</textarea>
                    @error('description') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="grid">
                    <div class="field span-4">
                        <label class="label" for="assignee_user_id">Assignee</label>
                        <select class="input @error('assignee_user_id') error-input @enderror" id="assignee_user_id" name="assignee_user_id">
                            <option value="">No direct assignee</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) $assigneeUserId === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assignee_user_id') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-4">
                        <label class="label" for="assignee_team_id">Assign Team</label>
                        <select class="input @error('assignee_team_id') error-input @enderror" id="assignee_team_id" name="assignee_team_id">
                            <option value="">No team assignee</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) $assigneeTeamId === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('assignee_team_id') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-4">
                        <label class="label" for="team_id">Task Team</label>
                        <select class="input @error('team_id') error-input @enderror" id="team_id" name="team_id">
                            <option value="">No team scope</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('team_id', $task->team_id) === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('team_id') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid">
                    <div class="field span-4">
                        <label class="label" for="task_priority_id">Priority</label>
                        <select class="input @error('task_priority_id') error-input @enderror" id="task_priority_id" name="task_priority_id">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}" @selected((string) old('task_priority_id', $task->task_priority_id) === (string) $priority->id)>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                        @error('task_priority_id') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-4">
                        <label class="label" for="task_category_id">Category</label>
                        <select class="input @error('task_category_id') error-input @enderror" id="task_category_id" name="task_category_id">
                            <option value="">No category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('task_category_id', $task->task_category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('task_category_id') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-4">
                        <label class="label" for="estimated_minutes">Estimate Minutes</label>
                        <input class="input @error('estimated_minutes') error-input @enderror" id="estimated_minutes" name="estimated_minutes" type="number" min="1" value="{{ old('estimated_minutes', $task->estimated_minutes) }}">
                        @error('estimated_minutes') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="due_at">Due Date and Time</label>
                    <input class="input @error('due_at') error-input @enderror" id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $task->due_at?->format('Y-m-d\TH:i')) }}">
                    @error('due_at') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <button class="button" type="submit">Save Task</button>
                    <a class="button secondary" href="{{ route('tasks.index') }}">Cancel</a>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
