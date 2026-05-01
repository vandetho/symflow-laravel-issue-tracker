<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laraflow\Eloquent\HasWorkflowTrait;

class Issue extends Model
{
    use HasWorkflowTrait;

    protected $fillable = [
        'reference',
        'reporter_id',
        'assignee_id',
        'title',
        'description',
        'priority',
        'label',
        'marking',
        'started_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'marking' => 'array',
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected function getDefaultWorkflowName(): string
    {
        return 'issue_tracking';
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IssueAuditLog::class)->orderByDesc('occurred_at');
    }

    /**
     * @return array<string>
     */
    public function activePlaces(): array
    {
        return $this->getWorkflowMarking()->getActivePlaces();
    }

    public function isInPlace(string $place): bool
    {
        return in_array($place, $this->activePlaces(), true);
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $places = $this->activePlaces();

                if (in_array('merged', $places, true)) {
                    return 'merged';
                }
                if (in_array('closed', $places, true)) {
                    return 'closed';
                }
                if (in_array('open', $places, true)) {
                    return 'open';
                }
                if (in_array('in_progress', $places, true)) {
                    return 'in_progress';
                }
                if (array_intersect(['code_approved', 'qa_approved'], $places)) {
                    return 'review_done';
                }

                return 'in_review';
            },
        );
    }
}
