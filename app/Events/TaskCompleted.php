<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Task $task)
    {
        $this->task->loadMissing(['company', 'creator', 'completedBy', 'status', 'priority', 'assignments.assignee', 'assignments.team']);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('company.'.$this->task->company_id),
            new PrivateChannel('user.'.$this->task->created_by),
        ];

        if ($this->task->completed_by) {
            $channels[] = new PrivateChannel('user.'.$this->task->completed_by);
        }

        foreach ($this->task->assignments as $assignment) {
            if ($assignment->assignee_team_id) {
                $channels[] = new PrivateChannel('team.'.$assignment->assignee_team_id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'task.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status?->name,
                'priority' => $this->task->priority?->name,
                'company_id' => $this->task->company_id,
                'creator' => $this->task->creator?->name,
                'due_at' => $this->task->due_at?->format('M j, g:i A'),
                'due_at_ts' => $this->task->due_at?->getTimestampMs(),
                'completed_by' => $this->task->completedBy?->name,
                'url' => route('tasks.show', $this->task),
            ],
        ];
    }
}
