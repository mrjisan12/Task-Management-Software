<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, int $id): bool {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('company.{companyId}', function ($user, int $companyId): bool {
    return $user->hasAnyRole(['super_admin', 'platform_admin'])
        || $user->companyMemberships()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->exists();
});

Broadcast::channel('team.{teamId}', function ($user, int $teamId): bool {
    return $user->hasAnyRole(['super_admin', 'platform_admin'])
        || $user->teamMemberships()
            ->where('team_id', $teamId)
            ->exists();
});
