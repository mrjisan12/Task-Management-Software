<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class SendTaskAssignedNotifications implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(TaskAssigned $event): void
    {
        $event->task->loadMissing(['assignments.assignee', 'assignments.team.users']);

        $users = collect();

        foreach ($event->task->assignments as $assignment) {
            if ($assignment->assignee) {
                $users->push($assignment->assignee);
            }

            if ($assignment->team) {
                $users = $users->merge($assignment->team->users);
            }
        }

        $users
            ->unique('id')
            ->each(function (User $user) use ($event): void {
                if ($this->alreadySent($user, $event->task->id)) {
                    return;
                }

                $user->notify(new TaskAssignedNotification($event->task));
            });
    }

    private function alreadySent(User $user, int $taskId): bool
    {
        $lockKey = "notification:task_assigned:user:{$user->id}:task:{$taskId}";

        if (! Cache::add($lockKey, true, now()->addDays(30))) {
            return true;
        }

        return $user->notifications()
            ->where('type', TaskAssignedNotification::class)
            ->where('data->event', 'task_assigned')
            ->where('data->task_id', $taskId)
            ->exists();
    }
}
