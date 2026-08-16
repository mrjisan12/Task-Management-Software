<?php

namespace App\Http\Controllers;

use App\Events\PointAwarded;
use App\Events\TaskCommented;
use App\Models\PointTransaction;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Services\TaskCompletionService;
use App\Services\TaskService;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    public function index(Request $request, CompanyContext $companyContext): View|RedirectResponse
    {
        $company = $companyContext->current($request->user());

        if (! $company) {
            return redirect()
                ->route('employee.dashboard')
                ->with('status', 'Join a company before viewing tasks.');
        }

        $assignedTasks = Task::query()
            ->with(['creator.profile', 'status', 'priority', 'team', 'assignments.assignee', 'assignments.team'])
            ->forCompany($company->id)
            ->assignedToUser($request->user());

        $pendingTasks = (clone $assignedTasks)
            ->incomplete()
            ->latest()
            ->limit(50)
            ->get();

        $completedTasks = (clone $assignedTasks)
            ->whereHas('status', fn ($query) => $query->where('slug', 'completed'))
            ->latest('completed_at')
            ->limit(50)
            ->get();

        $sentTasks = Task::query()
            ->with(['creator.profile', 'status', 'priority', 'team', 'assignments.assignee', 'assignments.team'])
            ->forCompany($company->id)
            ->where('created_by', $request->user()->id)
            ->incomplete()
            ->latest()
            ->limit(50)
            ->get();

        return view('employee.tasks.index', [
            'company' => $company,
            'pendingTasks' => $pendingTasks,
            'sentTasks' => $sentTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    public function create(Request $request, CompanyContext $companyContext): View|RedirectResponse
    {
        $company = $companyContext->current($request->user());

        if (! $company) {
            return redirect()
                ->route('employee.dashboard')
                ->with('status', 'Join a company before creating tasks.');
        }

        return view('employee.tasks.create', [
            'company' => $company,
            ...$this->formData($company->id, $request->user()->id),
        ]);
    }

    public function store(Request $request, CompanyContext $companyContext, TaskService $taskService): RedirectResponse
    {
        $company = $companyContext->current($request->user());

        abort_unless($company, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assignee_team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'task_status_id' => ['nullable', 'integer', 'exists:task_statuses,id'],
            'task_priority_id' => ['nullable', 'integer', 'exists:task_priorities,id'],
            'task_category_id' => ['nullable', 'integer', 'exists:task_categories,id'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'attachments' => ['nullable', 'array', 'max:12'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        abort_if((int) ($validated['assignee_user_id'] ?? 0) === $request->user()->id, 422);

        $task = $taskService->createForUser($company, $request->user(), $validated);
        $this->storeAttachments($task, $request);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created successfully.');
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorize('update', $task);

        return view('employee.tasks.edit', [
            'task' => $task->load(['assignments.assignee', 'assignments.team']),
            'company' => $task->company,
            ...$this->formData($task->company_id, $request->user()->id),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate($this->taskRules());

        abort_if((int) ($validated['assignee_user_id'] ?? 0) === $request->user()->id, 422);

        $this->ensureEditableCompanyTarget($task->company_id, $validated['assignee_user_id'] ?? null, $validated['assignee_team_id'] ?? null);

        $task->update([
            'team_id' => $validated['team_id'] ?? null,
            'task_priority_id' => $validated['task_priority_id'] ?? null,
            'task_category_id' => $validated['task_category_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
        ]);

        $task->assignments()->delete();

        if (! empty($validated['assignee_user_id'])) {
            $task->assignments()->create([
                'assignee_user_id' => $validated['assignee_user_id'],
                'assigned_by' => $request->user()->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);
        }

        if (! empty($validated['assignee_team_id'])) {
            $task->assignments()->create([
                'assignee_team_id' => $validated['assignee_team_id'],
                'assigned_by' => $request->user()->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);
        }

        $this->storeAttachments($task, $request);

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted successfully.');
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorize('view', $task);

        return view('employee.tasks.show', [
            'task' => $task->load([
                'company',
                'creator',
                'status',
                'priority',
                'category',
                'team',
                'assignments.assignee',
                'assignments.team',
                'attachments' => fn ($query) => $query->oldest(),
                'comments' => fn ($query) => $query->oldest(),
                'comments.user.profile',
            ]),
        ]);
    }

    public function viewAttachment(TaskAttachment $attachment): BinaryFileResponse
    {
        $this->authorize('view', $attachment->task);

        abort_unless(str_starts_with((string) $attachment->mime_type, 'image/'), 404);

        return response()->file($this->attachmentPath($attachment), [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    public function downloadAttachment(TaskAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->task);

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function comment(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $comment->load(['user.profile', 'task.creator', 'task.assignments.assignee', 'task.assignments.team.users']);

        $event = new TaskCommented($comment);
        TaskCommented::dispatch($comment);

        if ($request->expectsJson()) {
            return response()->json([
                'comment' => $event->payload(),
            ]);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Comment added successfully.');
    }

    public function complete(Request $request, Task $task, TaskCompletionService $taskCompletionService): RedirectResponse
    {
        $this->authorize('complete', $task);

        $validated = $request->validate([
            'completion_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $taskCompletionService->complete($task, $request->user(), $validated['completion_comment'] ?? null);

        $reward = PointTransaction::query()
            ->where('idempotency_key', "task:{$task->id}:completed:user:{$request->user()->id}")
            ->first();

        return redirect()
            ->route('tasks.show', $task)
            ->with('reward_popups', $reward ? [(new PointAwarded($reward))->payload()] : [])
            ->with('status', 'Task completed successfully.');
    }

    private function taskRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assignee_team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'task_priority_id' => ['nullable', 'integer', 'exists:task_priorities,id'],
            'task_category_id' => ['nullable', 'integer', 'exists:task_categories,id'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'attachments' => ['nullable', 'array', 'max:12'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    private function storeAttachments(Task $task, Request $request): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $disk = 'local';
            $path = $file->store("task-attachments/{$task->id}", $disk);

            $task->attachments()->create([
                'uploaded_by' => $request->user()->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function attachmentPath(TaskAttachment $attachment): string
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->path($attachment->path);
    }

    private function formData(int $companyId, ?int $excludeUserId = null): array
    {
        return [
            'users' => User::query()
                ->whereHas('companyMemberships', fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('status', 'active'))
                ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
                ->orderBy('name')
                ->get(),
            'teams' => Team::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'statuses' => TaskStatus::query()
                ->where(fn ($query) => $query->where('company_id', $companyId)->orWhereNull('company_id'))
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'priorities' => TaskPriority::query()
                ->where(fn ($query) => $query->where('company_id', $companyId)->orWhereNull('company_id'))
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'categories' => TaskCategory::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    private function ensureEditableCompanyTarget(int $companyId, ?int $assigneeUserId, ?int $assigneeTeamId): void
    {
        if ($assigneeUserId) {
            abort_unless(User::query()
                ->whereKey($assigneeUserId)
                ->whereHas('companyMemberships', fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('status', 'active'))
                ->exists(), 422);
        }

        if ($assigneeTeamId) {
            abort_unless(Team::query()
                ->whereKey($assigneeTeamId)
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->exists(), 422);
        }
    }
}
