<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CompanyContext
{
    public const SESSION_KEY = 'active_company_id';

    public function current(?User $user = null): ?Company
    {
        $user ??= Auth::user();

        if (! $user) {
            return null;
        }

        $companyId = session(self::SESSION_KEY);

        if ($companyId && $this->userBelongsToCompany($user, (int) $companyId)) {
            return Company::query()->whereKey($companyId)->first();
        }

        $company = $user->activeCompanies()->first();

        if ($company) {
            $this->set($company);
        }

        return $company;
    }

    public function set(Company $company): void
    {
        session([self::SESSION_KEY => $company->id]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function userBelongsToCompany(User $user, int $companyId): bool
    {
        return $user->companyMemberships()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->exists();
    }
}
