<?php

namespace App\Providers;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\CompanyJoinRequest;
use App\Models\Department;
use App\Models\Leaderboard;
use App\Models\Level;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\UserAchievement;
use App\Models\UserBadge;
use App\Models\UserPointSummary;
use App\Support\AdminCompanyScope;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->companyOwnedModels() as $model) {
            $model::saving(function ($record): void {
                if (! AdminCompanyScope::isCompanyAdmin() || AdminCompanyScope::isPlatformAdmin()) {
                    return;
                }

                $companyId = AdminCompanyScope::companyId();

                abort_unless($companyId, 403);

                $record->company_id = $companyId;
            });
        }
    }

    private function companyOwnedModels(): array
    {
        return [
            Achievement::class,
            Badge::class,
            CompanyJoinRequest::class,
            Department::class,
            Leaderboard::class,
            Level::class,
            PointRule::class,
            PointTransaction::class,
            Streak::class,
            Task::class,
            TaskCategory::class,
            TaskPriority::class,
            TaskStatus::class,
            Team::class,
            UserAchievement::class,
            UserBadge::class,
            UserPointSummary::class,
        ];
    }
}
