<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Streak;
use App\Models\User;
use Carbon\CarbonInterface;

class StreakService
{
    public function recordActivity(Company $company, User $user, CarbonInterface $activityAt): Streak
    {
        $activityDate = $activityAt->toDateString();

        $streak = Streak::query()->firstOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0],
        );

        if ($streak->last_activity_on?->toDateString() === $activityDate) {
            return $streak;
        }

        $yesterday = $activityAt->copy()->subDay()->toDateString();

        if ($streak->last_activity_on?->toDateString() === $yesterday) {
            $streak->current_streak += 1;
        } else {
            $streak->current_streak = 1;
            $streak->streak_started_on = $activityDate;
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_on = $activityDate;
        $streak->save();

        return $streak;
    }
}
