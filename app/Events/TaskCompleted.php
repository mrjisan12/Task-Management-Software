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
        $this->task->loadMissing(['company', 'creator.profile', 'completedBy', 'status', 'priority', 'assignments.assignee', 'assignments.team']);
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
                'creator_photo' => $this->task->creator?->profile?->photoUrl(),
                'creator_initial' => str($this->task->creator?->name ?? 'U')->substr(0, 1)->upper()->toString(),
                'due_at' => $this->task->due_at?->format('M j, g:i A'),
                'due_at_ts' => $this->task->due_at?->getTimestampMs(),
                'assignees' => $this->assignees(),
                'assigned_to_you_text' => ($this->task->creator?->name ?? 'Someone').' assigned this to you',
                'sent_text' => 'You assigned this to '.($this->assignees() ?: 'No assignee'),
                'completed_by' => $this->task->completedBy?->name,
                'url' => route('tasks.show', $this->task),
                'edit_url' => route('tasks.edit', $this->task),
                'delete_url' => route('tasks.destroy', $this->task),
            ],
        ];
    }

    private function assignees(): string
    {
        return $this->task->assignments
            ->map(fn ($assignment) => $assignment->assignee?->name ?? $assignment->team?->name)
            ->filter()
            ->values()
            ->join(', ');
    }
}
