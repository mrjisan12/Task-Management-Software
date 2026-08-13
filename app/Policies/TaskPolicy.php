<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->companyMemberships()->where('status', 'active')->exists()
            || $user->hasAnyRole(['super_admin', 'platform_admin']);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->canAccessCompany($user, $task->company_id)
            && ($user->hasAnyRole(['super_admin', 'platform_admin', 'company_admin', 'manager', 'team_lead'])
                || $task->creator?->is($user)
                || Task::query()->whereKey($task->id)->assignedToUser($user)->exists());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'platform_admin', 'company_admin', 'manager', 'team_lead', 'employee']);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canAccessCompany($user, $task->company_id)
            && ($user->hasAnyRole(['super_admin', 'platform_admin', 'company_admin', 'manager', 'team_lead'])
                || $task->creator?->is($user));
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->canAccessCompany($user, $task->company_id)
            && ! $task->isCompleted()
            && Task::query()
                ->whereKey($task->id)
                ->assignedToUser($user)
                ->exists();
    }

    private function canAccessCompany(User $user, int $companyId): bool
    {
        return $user->hasAnyRole(['super_admin', 'platform_admin'])
            || $user->companyMemberships()
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->exists();
    }
}
