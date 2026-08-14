<?php

namespace App\Notifications;

use App\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TaskComment $comment)
    {
        $this->afterCommit();
        $this->onQueue('notifications');
        $this->comment->loadMissing(['task', 'user']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'task',
            'event' => 'task_comment',
            'title' => 'New Task Comment',
            'body' => ($this->comment->user?->name ?? 'Someone').' commented on: '.$this->comment->task->title,
            'company_id' => $this->comment->task->company_id,
            'task_id' => $this->comment->task_id,
            'comment_id' => $this->comment->id,
            'actor_id' => $this->comment->user_id,
            'sound' => 'task_comment',
            'priority' => 'normal',
            'action_url' => route('tasks.show', $this->comment->task),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
