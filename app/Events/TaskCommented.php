<?php

namespace App\Events;

use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TaskCommented implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TaskComment $comment)
    {
        $this->comment->loadMissing([
            'user.profile',
            'task.creator',
            'task.assignments.assignee',
            'task.assignments.team.users',
        ]);
    }

    public function broadcastOn(): array
    {
        return $this->participants()
            ->map(fn (User $user) => new PrivateChannel('user.'.$user->id))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'task.commented';
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => $this->payload(),
        ];
    }

    public function payload(): array
    {
        return [
            'id' => $this->comment->id,
            'task_id' => $this->comment->task_id,
            'body' => $this->comment->body,
            'user_id' => $this->comment->user_id,
            'user_name' => $this->comment->user?->name,
            'user_photo' => $this->comment->user?->profile?->photoUrl(),
            'user_initial' => str($this->comment->user?->name ?? 'U')->substr(0, 1)->upper()->toString(),
            'created_at' => $this->comment->created_at?->format('M j, g:i A'),
            'created_at_human' => $this->comment->created_at?->diffForHumans(),
            'task_url' => route('tasks.show', $this->comment->task),
        ];
    }

    public function participants(): Collection
    {
        $users = collect();

        if ($this->comment->task->creator) {
            $users->push($this->comment->task->creator);
        }

        foreach ($this->comment->task->assignments as $assignment) {
            if ($assignment->assignee) {
                $users->push($assignment->assignee);
            }

            if ($assignment->team) {
                $users = $users->merge($assignment->team->users);
            }
        }

        return $users->unique('id')->values();
    }
}
