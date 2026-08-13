<?php

namespace App\Services;

use App\Events\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskCompletionService
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly PointService $pointService,
        private readonly GamificationProgressService $gamificationProgressService,
    )
    {
    }

    public function complete(Task $task, User $user, ?string $comment = null): Task
    {
        if ($task->isCompleted()) {
            throw ValidationException::withMessages([
                'task' => 'This task is already completed.',
            ]);
        }

        if (! $this->canComplete($task, $user)) {
            throw ValidationException::withMessages([
                'task' => 'You are not assigned to this task.',
            ]);
        }

        $completedTask = DB::transaction(function () use ($task, $user, $comment): Task {
            $completedStatus = $this->taskService->defaultStatus($task->company, 'completed');

            $task->update([
                'task_status_id' => $completedStatus->id,
                'completed_by' => $user->id,
                'completed_at' => now(),
                'completion_comment' => $comment,
            ]);

            $task->assignments()
                ->where(function ($query) use ($user): void {
                    $query->where('assignee_user_id', $user->id)
                        ->orWhereHas('team.users', fn ($teamUserQuery) => $teamUserQuery->where('users.id', $user->id));
                })
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            if ($comment) {
                $task->comments()->create([
                    'user_id' => $user->id,
                    'body' => $comment,
                ]);
            }

            $task->loadMissing(['company', 'creator']);

            $this->pointService->awardForRule(
                company: $task->company,
                user: $user,
                ruleKey: 'task_completed',
                source: 'task_completion',
                sourceId: (string) $task->id,
                task: $task,
                description: 'Task completed: '.$task->title,
                idempotencyKey: "task:{$task->id}:completed:user:{$user->id}",
            );

            $this->pointService->incrementTasksCompleted($task->company, $user, $task);

            if ($task->creator && $task->creator->id !== $user->id) {
                $this->pointService->awardForRule(
                    company: $task->company,
                    user: $task->creator,
                    ruleKey: 'task_assignment_success',
                    source: 'task_completion',
                    sourceId: (string) $task->id,
                    task: $task,
                    description: 'Assigned task completed: '.$task->title,
                    idempotencyKey: "task:{$task->id}:assignment-success:user:{$task->creator->id}",
                );
            }

            if ($task->due_at && $task->completed_at && $task->completed_at->lessThanOrEqualTo($task->due_at)) {
                $this->pointService->awardForRule(
                    company: $task->company,
                    user: $user,
                    ruleKey: 'on_time_bonus',
                    source: 'task_completion',
                    sourceId: (string) $task->id,
                    task: $task,
                    description: 'On-time bonus: '.$task->title,
                    idempotencyKey: "task:{$task->id}:on-time:user:{$user->id}",
                );
            }

            if ($task->due_at && $task->completed_at && $task->completed_at->lessThanOrEqualTo($task->due_at->copy()->subDay())) {
                $this->pointService->awardForRule(
                    company: $task->company,
                    user: $user,
                    ruleKey: 'early_completion_bonus',
                    source: 'task_completion',
                    sourceId: (string) $task->id,
                    task: $task,
                    description: 'Early completion bonus: '.$task->title,
                    idempotencyKey: "task:{$task->id}:early:user:{$user->id}",
                );
            }

            $this->gamificationProgressService->handleTaskCompleted($task, $user);

            return $task->refresh();
        });

        DB::afterCommit(fn () => TaskCompleted::dispatch($completedTask));

        return $completedTask;
    }

    private function canComplete(Task $task, User $user): bool
    {
        return Task::query()
            ->whereKey($task->id)
            ->assignedToUser($user)
            ->exists();
    }
}
