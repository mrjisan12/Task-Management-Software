<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Leaderboard;
use App\Models\Level;
use App\Models\Streak;
use App\Models\Task;
use App\Models\UserPointSummary;
use App\Services\LevelService;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function __invoke(Request $request, CompanyContext $companyContext, LevelService $levelService): View
    {
        $user = $request->user();
        $company = $companyContext->current($user);

        $memberships = $user->companyMemberships()
            ->with('company')
            ->latest()
            ->get();

        $joinRequests = $user->companyJoinRequests()
            ->with('company')
            ->latest()
            ->get();

        $taskStats = [
            'pending' => 0,
            'completed' => 0,
            'overdue' => 0,
            'today' => 0,
        ];

        $recentTasks = collect();
        $pointSummary = null;
        $currentLevel = null;
        $nextLevel = null;
        $streak = null;
        $earnedBadges = collect();
        $earnedAchievements = collect();
        $availableBadges = collect();
        $availableAchievements = collect();
        $availableLevels = collect();
        $leaderboardEntries = collect();
        $monthlyRank = null;

        if ($company) {
            $assignedTasks = Task::query()
                ->forCompany($company->id)
                ->assignedToUser($user);

            $taskStats = [
                'pending' => (clone $assignedTasks)->whereDoesntHave('status', fn ($query) => $query->where('slug', 'completed'))->count(),
                'completed' => (clone $assignedTasks)->whereHas('status', fn ($query) => $query->where('slug', 'completed'))->count(),
                'overdue' => (clone $assignedTasks)->whereNull('completed_at')->where('due_at', '<', now())->count(),
                'today' => (clone $assignedTasks)->whereDate('due_at', today())->count(),
            ];

            $recentTasks = (clone $assignedTasks)
                ->with(['status', 'priority'])
                ->latest()
                ->limit(5)
                ->get();

            $pointSummary = UserPointSummary::query()->firstOrCreate(
                ['company_id' => $company->id, 'user_id' => $user->id],
                ['total_points' => 0, 'monthly_points' => 0, 'xp' => 0, 'tasks_completed' => 0],
            );

            $currentLevel = $levelService->currentLevel($company, $pointSummary->xp);
            $nextLevel = $levelService->nextLevel($company, $pointSummary->xp);

            $streak = Streak::query()->firstOrCreate(
                ['company_id' => $company->id, 'user_id' => $user->id],
                ['current_streak' => 0, 'longest_streak' => 0],
            );

            $earnedBadges = $user->badges()
                ->with('badge')
                ->where('company_id', $company->id)
                ->latest('earned_at')
                ->limit(6)
                ->get();

            $earnedAchievements = $user->achievements()
                ->with('achievement')
                ->where('company_id', $company->id)
                ->latest('earned_at')
                ->limit(5)
                ->get();

            $earnedBadgeIds = $user->badges()
                ->where('company_id', $company->id)
                ->pluck('badge_id');

            $earnedAchievementIds = $user->achievements()
                ->where('company_id', $company->id)
                ->pluck('achievement_id');

            $availableBadges = $this->badgesFor($company)
                ->where('is_active', true)
                ->whereNotIn('id', $earnedBadgeIds)
                ->orderBy('name')
                ->get();

            $availableAchievements = $this->achievementsFor($company)
                ->where('is_active', true)
                ->whereNotIn('id', $earnedAchievementIds)
                ->orderBy('name')
                ->get();

            $availableLevels = $this->levelsFor($company)
                ->where('is_active', true)
                ->orderBy('required_xp')
                ->get();

            $leaderboard = Leaderboard::query()
                ->with(['entries.user'])
                ->where('company_id', $company->id)
                ->where('period', 'monthly')
                ->where('starts_on', now()->startOfMonth()->toDateString())
                ->first();

            if ($leaderboard) {
                $leaderboardEntries = $leaderboard->entries()
                    ->with('user')
                    ->orderBy('rank')
                    ->limit(5)
                    ->get();

                $monthlyRank = $leaderboard->entries()
                    ->where('user_id', $user->id)
                    ->value('rank');
            }
        }

        return view('employee.dashboard', [
            'user' => $user,
            'company' => $company,
            'memberships' => $memberships,
            'joinRequests' => $joinRequests,
            'taskStats' => $taskStats,
            'recentTasks' => $recentTasks,
            'pointSummary' => $pointSummary,
            'currentLevel' => $currentLevel,
            'nextLevel' => $nextLevel,
            'streak' => $streak,
            'earnedBadges' => $earnedBadges,
            'earnedAchievements' => $earnedAchievements,
            'availableBadges' => $availableBadges,
            'availableAchievements' => $availableAchievements,
            'availableLevels' => $availableLevels,
            'leaderboardEntries' => $leaderboardEntries,
            'monthlyRank' => $monthlyRank,
        ]);
    }

    private function badgesFor($company)
    {
        $hasCompanyBadges = Badge::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        return Badge::query()
            ->where('company_id', $hasCompanyBadges ? $company->id : null);
    }

    private function achievementsFor($company)
    {
        $hasCompanyAchievements = Achievement::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        return Achievement::query()
            ->where('company_id', $hasCompanyAchievements ? $company->id : null);
    }

    private function levelsFor($company)
    {
        $hasCompanyLevels = Level::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        return Level::query()
            ->where('company_id', $hasCompanyLevels ? $company->id : null);
    }
}
