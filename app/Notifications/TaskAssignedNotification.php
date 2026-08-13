<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task)
    {
        $this->afterCommit();
        $this->onQueue('notifications');
        $this->task->loadMissing(['creator', 'company']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'task',
            'event' => 'task_assigned',
            'title' => 'New Task Assigned',
            'body' => $this->task->creator->name.' assigned you: '.$this->task->title,
            'company_id' => $this->task->company_id,
            'task_id' => $this->task->id,
            'actor_id' => $this->task->created_by,
            'sound' => 'task_assigned',
            'priority' => 'normal',
            'action_url' => route('tasks.show', $this->task),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
