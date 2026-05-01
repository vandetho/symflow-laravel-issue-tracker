<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueAuditLog extends Model
{
    protected $fillable = [
        'issue_id',
        'actor_id',
        'event',
        'transition',
        'marking_before',
        'marking_after',
        'reason',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'marking_before' => 'array',
            'marking_after' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
