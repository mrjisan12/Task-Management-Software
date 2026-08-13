<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task, public string $audience = 'creator')
    {
        $this->afterCommit();
        $this->onQueue('notifications');
        $this->task->loadMissing(['completedBy', 'company']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isCompleter = $this->audience === 'completer';
        $body = $isCompleter
            ? 'You completed: '.$this->task->title
            : ($this->task->completedBy?->name ?? 'Someone').' completed: '.$this->task->title;

        return [
            'category' => 'task',
            'event' => 'task_completed',
            'title' => 'Task Completed',
            'body' => $body,
            'company_id' => $this->task->company_id,
            'task_id' => $this->task->id,
            'actor_id' => $this->task->completed_by,
            'sound' => 'task_completed',
            'priority' => 'normal',
            'action_url' => route('tasks.show', $this->task),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
