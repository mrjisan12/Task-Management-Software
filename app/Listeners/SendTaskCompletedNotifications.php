<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class SendTaskCompletedNotifications implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(TaskCompleted $event): void
    {
        $event->task->loadMissing(['creator', 'completedBy']);

        if ($event->task->completedBy && ! $this->alreadySent($event->task->completedBy, $event->task->id)) {
            $event->task->completedBy->notify(new TaskCompletedNotification($event->task, 'completer'));
        }

        if ($event->task->creator
            && $event->task->creator->id !== $event->task->completed_by
            && ! $this->alreadySent($event->task->creator, $event->task->id)) {
            $event->task->creator->notify(new TaskCompletedNotification($event->task, 'creator'));
        }
    }

    private function alreadySent(User $user, int $taskId): bool
    {
        $lockKey = "notification:task_completed:user:{$user->id}:task:{$taskId}";

        if (! Cache::add($lockKey, true, now()->addDays(30))) {
            return true;
        }

        return $user->notifications()
            ->where('type', TaskCompletedNotification::class)
            ->where('data->event', 'task_completed')
            ->where('data->task_id', $taskId)
            ->exists();
    }
}
