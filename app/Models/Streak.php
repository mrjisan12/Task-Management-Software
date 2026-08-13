<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Streak extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'current_streak', 'longest_streak',
        'streak_started_on', 'last_activity_on', 'freeze_count',
    ];

    protected function casts(): array
    {
        return [
            'streak_started_on' => 'date',
            'last_activity_on' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
