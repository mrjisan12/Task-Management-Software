<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    protected $fillable = [
        'company_id', 'name', 'slug', 'description', 'icon', 'rule_key',
        'requirements', 'points_reward', 'is_repeatable', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'is_repeatable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
