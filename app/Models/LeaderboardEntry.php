<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardEntry extends Model
{
    protected $fillable = [
        'leaderboard_id', 'user_id', 'team_id', 'rank', 'points',
        'tasks_completed', 'completion_rate', 'on_time_rate', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'completion_rate' => 'decimal:2',
            'on_time_rate' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function leaderboard(): BelongsTo
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
