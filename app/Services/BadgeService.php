<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Company;
use App\Models\Streak;
use App\Models\Task;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserPointSummary;

class BadgeService
{
    public function evaluateForTaskCompletion(Company $company, User $user, Task $task): array
    {
        $earned = [];

        $badges = Badge::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('is_active', true)
            ->get();

        foreach ($badges as $badge) {
            if ($this->qualifies($badge, $company, $user, $task)) {
                $award = UserBadge::query()->firstOrCreate(
                    ['company_id' => $company->id, 'user_id' => $user->id, 'badge_id' => $badge->id],
                    ['earned_at' => now(), 'metadata' => ['trigger_task_id' => $task->id]],
                );

                if ($award->wasRecentlyCreated) {
                    $earned[] = $award->load('badge');
                }
            }
        }

        return $earned;
    }

    private function qualifies(Badge $badge, Company $company, User $user, Task $task): bool
    {
        $requirements = $badge->requirements ?? [];
        $threshold = (int) ($requirements['threshold'] ?? 1);

        return match ($badge->rule_key) {
            'tasks_completed_count' => (int) UserPointSummary::query()
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->value('tasks_completed') >= $threshold,
            'on_time_tasks_count' => Task::query()
                ->forCompany($company->id)
                ->where('completed_by', $user->id)
                ->whereNotNull('completed_at')
                ->whereColumn('completed_at', '<=', 'due_at')
                ->count() >= $threshold,
            'streak_days' => (int) Streak::query()
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->value('current_streak') >= $threshold,
            'monthly_points' => (int) UserPointSummary::query()
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->value('monthly_points') >= $threshold,
            default => false,
        };
    }
}
