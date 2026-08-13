<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementService
{
    public function evaluateForTaskCompletion(Company $company, User $user, Task $task): array
    {
        $earned = [];

        $achievements = Achievement::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('is_active', true)
            ->get();

        foreach ($achievements as $achievement) {
            if (! $achievement->is_repeatable && UserAchievement::query()
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->exists()) {
                continue;
            }

            if (! $this->qualifies($achievement, $company, $user, $task)) {
                continue;
            }

            $earned[] = UserAchievement::query()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'earned_at' => now(),
                'metadata' => ['trigger_task_id' => $task->id],
            ])->load('achievement');
        }

        return $earned;
    }

    private function qualifies(Achievement $achievement, Company $company, User $user, Task $task): bool
    {
        $requirements = $achievement->requirements ?? [];

        return match ($achievement->rule_key) {
            'deadline_sprinter' => $task->due_at
                && $task->completed_at
                && $task->completed_at->greaterThanOrEqualTo($task->due_at->copy()->subMinutes((int) ($requirements['minutes_before_due'] ?? 60)))
                && $task->completed_at->lessThanOrEqualTo($task->due_at),
            'very_late_completion' => $task->due_at
                && $task->completed_at
                && $task->completed_at->greaterThan($task->due_at->copy()->addHours((int) ($requirements['hours_late'] ?? 24))),
            'daily_task_burst' => Task::query()
                ->forCompany($company->id)
                ->where('completed_by', $user->id)
                ->whereDate('completed_at', $task->completed_at?->toDateString() ?? today())
                ->count() >= (int) ($requirements['threshold'] ?? 3),
            default => false,
        };
    }
}
