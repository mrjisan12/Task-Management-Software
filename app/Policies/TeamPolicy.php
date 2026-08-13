<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teams.view');
    }

    public function view(User $user, Team $team): bool
    {
        return $user->can('teams.view') && $this->canAccessCompany($user, $team->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('teams.create');
    }

    public function update(User $user, Team $team): bool
    {
        return $user->can('teams.update') && $this->canAccessCompany($user, $team->company_id);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->can('teams.delete') && $this->canAccessCompany($user, $team->company_id);
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
