<x-layouts.app>
    <div class="grid">
        <section class="panel span-8">
            <h1 class="title">Create Task</h1>
            <p class="subtitle">{{ $company->name }}</p>

            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf

                <div class="field">
                    <label class="label" for="title">Title</label>
                    <input class="input" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="label" for="description">Description</label>
                    <textarea class="input" id="description" name="description" style="height: 120px; padding-top: 10px;">{{ old('description') }}</textarea>
                    @error('description') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="grid">
                    <div class="field span-4">
                        <label class="label" for="assignee_user_id">Assignee</label>
                        <select class="input" id="assignee_user_id" name="assignee_user_id">
                            <option value="">No direct assignee</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('assignee_user_id') === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field span-4">
                        <label class="label" for="assignee_team_id">Assign Team</label>
                        <select class="input" id="assignee_team_id" name="assignee_team_id">
                            <option value="">No team assignee</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('assignee_team_id') === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field span-4">
                        <label class="label" for="team_id">Task Team</label>
                        <select class="input" id="team_id" name="team_id">
                            <option value="">No team scope</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid">
                    <div class="field span-4">
                        <label class="label" for="task_priority_id">Priority</label>
                        <select class="input" id="task_priority_id" name="task_priority_id">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}" @selected((string) old('task_priority_id') === (string) $priority->id)>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field span-4">
                        <label class="label" for="task_category_id">Category</label>
                        <select class="input" id="task_category_id" name="task_category_id">
                            <option value="">No category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('task_category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field span-4">
                        <label class="label" for="estimated_minutes">Estimate Minutes</label>
                        <input class="input" id="estimated_minutes" name="estimated_minutes" type="number" min="1" value="{{ old('estimated_minutes') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="due_at">Due Date and Time</label>
                    <input class="input" id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at') }}">
                </div>

                <div class="field">
                    <button class="button" type="submit">Create Task</button>
                    <a class="button secondary" href="{{ route('tasks.index') }}">Cancel</a>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
