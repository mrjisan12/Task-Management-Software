<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Level;

class LevelService
{
    public function currentLevel(Company $company, int $xp): ?Level
    {
        return $this->levelsFor($company)
            ->where('is_active', true)
            ->where('required_xp', '<=', $xp)
            ->orderByDesc('required_xp')
            ->first();
    }

    public function nextLevel(Company $company, int $xp): ?Level
    {
        return $this->levelsFor($company)
            ->where('is_active', true)
            ->where('required_xp', '>', $xp)
            ->orderBy('required_xp')
            ->first();
    }

    private function levelsFor(Company $company)
    {
        $hasCompanyLevels = Level::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        return Level::query()
            ->where('company_id', $hasCompanyLevels ? $company->id : null);
    }
}
