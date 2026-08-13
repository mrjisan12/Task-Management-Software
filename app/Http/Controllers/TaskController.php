<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TaskCompletionService;
use App\Services\TaskService;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            ->whereDoesntHave('status', fn ($query) => $query->where('slug', 'completed'))
            ->latest()
            ->limit(50)
            ->get();

        $completedTasks = (clone $assignedTasks)
            ->whereHas('status', fn ($query) => $query->where('slug', 'completed'))
            ->latest('completed_at')
            ->limit(50)
            ->get();

        return view('employee.tasks.index', [
            'company' => $company,
            'pendingTasks' => $pendingTasks,
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
            'users' => User::query()
                ->whereHas('companyMemberships', fn ($query) => $query
                    ->where('company_id', $company->id)
                    ->where('status', 'active'))
                ->orderBy('name')
                ->get(),
            'teams' => $company->teams()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => TaskStatus::query()->where(fn ($query) => $query->where('company_id', $company->id)->orWhereNull('company_id'))->where('is_active', true)->orderBy('sort_order')->get(),
            'priorities' => TaskPriority::query()->where(fn ($query) => $query->where('company_id', $company->id)->orWhereNull('company_id'))->where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => TaskCategory::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(),
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
            'due_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ]);

        $task = $taskService->createForUser($company, $request->user(), $validated);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created successfully.');
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorize('view', $task);

        return view('employee.tasks.show', [
            'task' => $task->load(['company', 'creator', 'status', 'priority', 'category', 'team', 'assignments.assignee', 'assignments.team', 'comments.user']),
        ]);
    }

    public function complete(Request $request, Task $task, TaskCompletionService $taskCompletionService): RedirectResponse
    {
        $this->authorize('complete', $task);

        $validated = $request->validate([
            'completion_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $taskCompletionService->complete($task, $request->user(), $validated['completion_comment'] ?? null);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task completed successfully.');
    }
}
