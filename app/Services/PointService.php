<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\Task;
use App\Models\User;
use App\Models\UserPointSummary;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function awardForRule(
        Company $company,
        User $user,
        string $ruleKey,
        string $source,
        ?string $sourceId = null,
        ?Task $task = null,
        array $metadata = [],
        ?string $description = null,
        ?string $idempotencyKey = null,
    ): ?PointTransaction {
        $rule = $this->rule($company, $ruleKey);

        if (! $rule || $rule->points === 0) {
            return null;
        }

        return $this->record(
            company: $company,
            user: $user,
            points: $rule->points,
            type: $rule->points >= 0 ? 'award' : 'deduction',
            source: $source,
            sourceId: $sourceId,
            task: $task,
            rule: $rule,
            description: $description ?? $rule->name,
            metadata: $metadata,
            idempotencyKey: $idempotencyKey ?? implode(':', [$company->id, $user->id, $ruleKey, $source, $sourceId ?? 'none']),
        );
    }

    public function record(
        Company $company,
        User $user,
        int $points,
        string $type,
        string $source,
        ?string $sourceId,
        ?Task $task,
        ?PointRule $rule,
        string $description,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): PointTransaction {
        $idempotencyKey ??= implode(':', [$company->id, $user->id, $type, $source, $sourceId ?? uniqid('', true)]);

        return DB::transaction(function () use ($company, $user, $points, $type, $source, $sourceId, $task, $rule, $description, $metadata, $idempotencyKey): PointTransaction {
            $existing = PointTransaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $transaction = PointTransaction::query()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'task_id' => $task?->id,
                'point_rule_id' => $rule?->id,
                'type' => $type,
                'source' => $source,
                'source_id' => $sourceId,
                'idempotency_key' => $idempotencyKey,
                'points' => $points,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            $summary = UserPointSummary::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ],
                [
                    'total_points' => 0,
                    'monthly_points' => 0,
                    'xp' => 0,
                    'tasks_completed' => 0,
                ],
            );

            $summary->increment('total_points', $points);
            $summary->increment('xp', max(0, $points));

            if ($transaction->created_at->isCurrentMonth()) {
                $summary->increment('monthly_points', $points);
            }

            $summary->forceFill(['last_recalculated_at' => now()])->save();

            return $transaction;
        });
    }

    public function incrementTasksCompleted(Company $company, User $user, Task $task): void
    {
        $idempotencyKey = implode(':', [$company->id, $user->id, 'tasks_completed', $task->id]);

        $marker = PointTransaction::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'task_id' => $task->id,
                'type' => 'metric',
                'source' => 'task_completion_metric',
                'source_id' => (string) $task->id,
                'points' => 0,
                'description' => 'Task completion metric marker',
                'metadata' => ['hidden' => true],
            ],
        );

        if (! $marker->wasRecentlyCreated) {
            return;
        }

        UserPointSummary::query()->firstOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['total_points' => 0, 'monthly_points' => 0, 'xp' => 0, 'tasks_completed' => 0],
        )->increment('tasks_completed');
    }

    public function rule(Company $company, string $key): ?PointRule
    {
        return PointRule::query()
            ->where(function ($query) use ($company): void {
                $query->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->where('key', $key)
            ->where('is_active', true)
            ->orderByRaw('company_id IS NULL')
            ->first();
    }
}
