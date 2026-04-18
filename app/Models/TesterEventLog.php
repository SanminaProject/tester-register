<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TesterEventLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $timestamps = false;

    protected $fillable = [
        'date',
        'description',
        'tester_id',
        'event_type',
        'created_by_user_id',
        'maintenance_schedule_id',
        'calibration_schedule_id',
        'resolved_date',
        'resolution_description',
        'resolved_by_user_id',
        'issue_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'resolved_date' => 'datetime',
        ];
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function issueStatusRelation(): BelongsTo
    {
        return $this->belongsTo(IssueStatus::class, 'issue_status');
    }

    public function scopeIssues(Builder $query): Builder
    {
        $issueTypeId = EventType::query()
            ->whereRaw('LOWER(name) = ?', ['issue'])
            ->value('id');

        if ($issueTypeId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('event_type', (int) $issueTypeId);
    }

    public function scopeActiveIssueRows(Builder $query): Builder
    {
        return $query
            ->issues()
            ->whereNotNull('issue_status')
            ->where('description', 'not like', '[HISTORY]%');
    }
}
