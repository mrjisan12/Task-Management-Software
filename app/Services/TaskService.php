<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Models\Company;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function createForUser(Company $company, User $creator, array $data): Task
    {
        $this->ensureCompanyMember($company, $creator);

        if (! empty($data['assignee_user_id'])) {
            $assignee = User::query()->findOrFail($data['assignee_user_id']);
            $this->ensureCompanyMember($company, $assignee);
        }

        $statusId = $data['task_status_id'] ?? $this->defaultStatus($company, 'pending')->id;
        $priorityId = $data['task_priority_id'] ?? $this->defaultPriority($company, 'medium')->id;

        $task = DB::transaction(function () use ($company, $creator, $data, $statusId, $priorityId): Task {
            $task = Task::query()->create([
                'company_id' => $company->id,
                'created_by' => $creator->id,
                'team_id' => $data['team_id'] ?? null,
                'task_status_id' => $statusId,
                'task_priority_id' => $priorityId,
                'task_category_id' => $data['task_category_id'] ?? null,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_at' => $data['due_at'] ?? null,
                'estimated_minutes' => $data['estimated_minutes'] ?? null,
            ]);

            if (! empty($data['assignee_user_id'])) {
                $task->assignments()->create([
                    'assignee_user_id' => $data['assignee_user_id'],
                    'assigned_by' => $creator->id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }

            if (! empty($data['assignee_team_id'])) {
                $task->assignments()->create([
                    'assignee_team_id' => $data['assignee_team_id'],
                    'assigned_by' => $creator->id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }

            return $task->refresh();
        });

        DB::afterCommit(fn () => TaskAssigned::dispatch($task));

        return $task;
    }

    public function defaultStatus(Company $company, string $slug): TaskStatus
    {
        return TaskStatus::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('slug', $slug)
            ->orderByRaw('company_id IS NULL')
            ->firstOrFail();
    }

    public function defaultPriority(Company $company, string $slug): TaskPriority
    {
        return TaskPriority::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('slug', $slug)
            ->orderByRaw('company_id IS NULL')
            ->firstOrFail();
    }

    private function ensureCompanyMember(Company $company, User $user): void
    {
        $isMember = $user->companyMemberships()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember && ! $user->hasAnyRole(['super_admin', 'platform_admin'])) {
            throw ValidationException::withMessages([
                'company' => 'User is not an active member of this company.',
            ]);
        }
    }
}
