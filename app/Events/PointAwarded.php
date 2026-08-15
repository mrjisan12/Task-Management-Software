<?php

namespace App\Events;

use App\Models\PointTransaction;
use App\Services\LevelService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointAwarded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PointTransaction $transaction)
    {
        $this->transaction->loadMissing(['company', 'user', 'task', 'rule']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->transaction->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'points.awarded';
    }

    public function broadcastWith(): array
    {
        return [
            'reward' => $this->payload(),
        ];
    }

    public function payload(): array
    {
        $summary = $this->transaction->user->pointSummaries()
            ->where('company_id', $this->transaction->company_id)
            ->first();

        $xp = (int) ($summary?->xp ?? 0);
        $levelService = app(LevelService::class);
        $currentLevel = $levelService->currentLevel($this->transaction->company, $xp);
        $nextLevel = $levelService->nextLevel($this->transaction->company, $xp);
        $points = (int) $this->transaction->points;
        $audience = $this->audience();

        return [
            'id' => $this->transaction->id,
            'audience' => $audience,
            'points' => $points,
            'xp_added' => max(0, $points),
            'total_points' => (int) ($summary?->total_points ?? 0),
            'total_xp' => $xp,
            'current_level' => $currentLevel?->name ?? 'Starting Level',
            'next_level' => $nextLevel?->name ?? 'Top Level',
            'next_level_xp' => $nextLevel?->required_xp,
            'xp_to_next' => $nextLevel ? max(0, $nextLevel->required_xp - $xp) : 0,
            'progress' => $this->progress($currentLevel?->required_xp ?? 0, $nextLevel?->required_xp, $xp),
            'title' => $audience === 'collaborator' ? 'Collaboration Momentum' : 'Excellent Work',
            'headline' => $this->headline($audience, $points),
            'message' => $this->message($audience),
            'task_title' => $this->transaction->task?->title,
            'description' => $this->transaction->description,
            'action_url' => route('tasks.index'),
        ];
    }

    private function audience(): string
    {
        return $this->transaction->source === 'task_completion'
            && $this->transaction->rule?->key === 'task_assignment_success'
            ? 'collaborator'
            : 'performer';
    }

    private function headline(string $audience, int $points): string
    {
        if ($audience === 'collaborator') {
            return "+{$points} points for helping work move forward.";
        }

        return "+{$points} points earned. Your progress just moved up.";
    }

    private function message(string $audience): string
    {
        if ($audience === 'collaborator') {
            return 'Your assignment helped the team stay aligned. Keep creating clear work and building smooth collaboration.';
        }

        return 'You completed real work and earned progress. Keep going, your consistency is building the next level.';
    }

    private function progress(int $currentRequiredXp, ?int $nextRequiredXp, int $xp): int
    {
        if (! $nextRequiredXp || $nextRequiredXp <= $currentRequiredXp) {
            return 100;
        }

        return (int) min(100, max(0, (($xp - $currentRequiredXp) / ($nextRequiredXp - $currentRequiredXp)) * 100));
    }
}
