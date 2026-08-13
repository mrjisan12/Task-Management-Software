<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPointSummary extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'total_points',
        'monthly_points',
        'xp',
        'tasks_completed',
        'last_recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'last_recalculated_at' => 'datetime',
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
