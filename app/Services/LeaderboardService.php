<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Leaderboard;
use App\Models\UserPointSummary;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function recalculateMonthly(Company $company, ?CarbonInterface $date = null): Leaderboard
    {
        $date ??= now();
        $startsOn = $date->copy()->startOfMonth()->toDateString();
        $endsOn = $date->copy()->endOfMonth()->toDateString();

        return DB::transaction(function () use ($company, $startsOn, $endsOn): Leaderboard {
            $leaderboard = Leaderboard::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'scope_type' => 'company',
                    'scope_id' => null,
                    'period' => 'monthly',
                    'starts_on' => $startsOn,
                ],
                ['ends_on' => $endsOn, 'status' => 'active'],
            );

            $summaries = UserPointSummary::query()
                ->with('user')
                ->where('company_id', $company->id)
                ->where('monthly_points', '>', 0)
                ->orderByDesc('monthly_points')
                ->orderByDesc('tasks_completed')
                ->get();

            $rank = 1;

            foreach ($summaries as $summary) {
                $leaderboard->entries()->updateOrCreate(
                    ['user_id' => $summary->user_id, 'team_id' => null],
                    [
                        'rank' => $rank++,
                        'points' => $summary->monthly_points,
                        'tasks_completed' => $summary->tasks_completed,
                        'completion_rate' => 0,
                        'on_time_rate' => 0,
                        'metadata' => ['user_name' => $summary->user?->name],
                    ],
                );
            }

            $leaderboard->entries()
                ->whereNotIn('user_id', $summaries->pluck('user_id'))
                ->delete();

            return $leaderboard->refresh();
        });
    }
}
