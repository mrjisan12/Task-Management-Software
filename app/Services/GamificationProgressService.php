<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class GamificationProgressService
{
    public function __construct(
        private readonly StreakService $streakService,
        private readonly BadgeService $badgeService,
        private readonly AchievementService $achievementService,
        private readonly LeaderboardService $leaderboardService,
    ) {
    }

    public function handleTaskCompleted(Task $task, User $user): void
    {
        $task->loadMissing('company');

        $this->streakService->recordActivity($task->company, $user, $task->completed_at ?? now());
        $this->badgeService->evaluateForTaskCompletion($task->company, $user, $task);
        $this->achievementService->evaluateForTaskCompletion($task->company, $user, $task);
        $this->leaderboardService->recalculateMonthly($task->company, $task->completed_at ?? now());
    }
}
