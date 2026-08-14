<?php

namespace App\Listeners;

use App\Events\TaskCommented;
use App\Models\User;
use App\Notifications\TaskCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskCommentNotifications implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(TaskCommented $event): void
    {
        $event->participants()
            ->reject(fn (User $user) => $user->id === $event->comment->user_id)
            ->each(fn (User $user) => $user->notify(new TaskCommentNotification($event->comment)));
    }
}
