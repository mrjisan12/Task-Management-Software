<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'task_id',
        'point_rule_id',
        'reversed_transaction_id',
        'type',
        'source',
        'source_id',
        'idempotency_key',
        'points',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PointRule::class, 'point_rule_id');
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class, 'reversed_transaction_id');
    }
}
