<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'parent_event_log_id',
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

    public function parentIssue(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_event_log_id');
    }

    public function solutionEntries(): HasMany
    {
        return $this->hasMany(self::class, 'parent_event_log_id');
    }

    public static function resolveEventTypeId(string $typeName): ?int
    {
        return EventType::query()
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($typeName))])
            ->value('id');
    }

    public function scopeIssues(Builder $query): Builder
    {
        $problemTypeId = self::resolveEventTypeId('problem');
        $legacyIssueTypeId = self::resolveEventTypeId('issue');

        $issueTypeIds = array_filter([
            $problemTypeId,
            $legacyIssueTypeId,
        ]);

        if ($issueTypeIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('event_type', $issueTypeIds);
    }

    public function scopeProblems(Builder $query): Builder
    {
        $problemTypeId = self::resolveEventTypeId('problem');

        if ($problemTypeId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('event_type', (int) $problemTypeId);
    }

    public function scopeSolutions(Builder $query): Builder
    {
        $solutionTypeId = self::resolveEventTypeId('solution');

        if ($solutionTypeId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('event_type', (int) $solutionTypeId);
    }

    public function scopeActiveIssueRows(Builder $query): Builder
    {
        $activeStatusId = IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', ['active'])
            ->value('id');

        return $query
            ->problems()
            ->when($activeStatusId !== null, function (Builder $builder) use ($activeStatusId) {
                $builder->where('issue_status', (int) $activeStatusId);
            })
            ->where('description', 'not like', '[HISTORY]%');
    }
}
