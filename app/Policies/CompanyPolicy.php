<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('companies.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('companies.view')
            && ($user->hasAnyRole(['super_admin', 'platform_admin']) || $this->belongsToCompany($user, $company));
    }

    public function create(User $user): bool
    {
        return $user->can('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('companies.update')
            && ($user->hasAnyRole(['super_admin', 'platform_admin']) || $this->belongsToCompany($user, $company));
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->can('companies.delete') && $user->hasAnyRole(['super_admin', 'platform_admin']);
    }

    private function belongsToCompany(User $user, Company $company): bool
    {
        return $user->companyMemberships()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->exists();
    }
}
