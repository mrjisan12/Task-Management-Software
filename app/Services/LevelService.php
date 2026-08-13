<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Level;

class LevelService
{
    public function currentLevel(Company $company, int $xp): ?Level
    {
        return Level::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('is_active', true)
            ->where('required_xp', '<=', $xp)
            ->orderByDesc('required_xp')
            ->first();
    }

    public function nextLevel(Company $company, int $xp): ?Level
    {
        return Level::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('is_active', true)
            ->where('required_xp', '>', $xp)
            ->orderBy('required_xp')
            ->first();
    }
}
