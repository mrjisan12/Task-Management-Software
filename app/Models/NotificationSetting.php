<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'sounds_enabled',
        'sound_volume',
        'channels',
    ];

    protected function casts(): array
    {
        return [
            'sounds_enabled' => 'boolean',
            'sound_volume' => 'integer',
            'channels' => 'array',
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
