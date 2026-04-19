<?php

namespace App\Livewire\Pages\Issues;

use App\Models\IssueStatus;
use App\Models\TesterEventLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddSolution extends Component
{
    public TesterEventLog $issue;

    public string $resolution_date = '';
    public string $resolution_description = '';
    public ?int $resolved_by_user_id = null;
    public ?int $status_id = null;

    public $users = [];
    public $statuses = [];

    public function mount($issueId): void
    {
        $this->issue = TesterEventLog::query()
            ->with(['tester', 'createdBy', 'issueStatusRelation', 'eventType'])
            ->problems()
            ->findOrFail((int) $issueId);

        $this->users = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function (User $user) {
                $label = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if ($label === '') {
                    $label = $user->email;
                }

                return [
                    'id' => $user->id,
                    'name' => $label,
                ];
            })
            ->toArray();

        $this->statuses = IssueStatus::query()->select('id', 'name')->orderBy('id')->get();

        $solvedId = (int) (IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', ['solved'])
            ->value('id') ?? 0);

        $lastSolution = TesterEventLog::query()
            ->solutions()
            ->where('parent_event_log_id', $this->issue->id)
            ->orderByDesc('date')
            ->first();

        $this->resolution_date = optional($lastSolution?->date ?? $this->issue->resolved_date ?? now())->toDateString();
        $this->resolution_description = (string) ($lastSolution?->description ?? $this->issue->resolution_description ?? '');
        $this->resolved_by_user_id = (int) ($lastSolution?->created_by_user_id ?? $this->issue->resolved_by_user_id ?? Auth::id() ?? 1);
        $this->status_id = $solvedId > 0 ? $solvedId : (int) ($this->issue->issue_status ?? 0);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'resolution_date' => ['required', 'date'],
            'resolution_description' => ['required', 'string', 'max:1000'],
            'resolved_by_user_id' => ['required', 'integer', 'exists:users,id'],
            'status_id' => ['required', 'integer', 'exists:issue_statuses,id'],
        ]);

        $solutionTypeId = TesterEventLog::resolveEventTypeId('solution');
        $problemTypeId = TesterEventLog::resolveEventTypeId('problem') ?? TesterEventLog::resolveEventTypeId('issue');

        if ($solutionTypeId === null || $problemTypeId === null) {
            $this->addError('status_id', 'Event types problem/solution are not configured.');
            return;
        }

        DB::transaction(function () use ($validated, $solutionTypeId, $problemTypeId) {
            $resolvedAt = Carbon::parse($validated['resolution_date'])->endOfDay();

            TesterEventLog::create([
                'date' => $resolvedAt,
                'description' => $validated['resolution_description'],
                'tester_id' => $this->issue->tester_id,
                'event_type' => (int) $solutionTypeId,
                'created_by_user_id' => (int) $validated['resolved_by_user_id'],
                'resolved_by_user_id' => (int) $validated['resolved_by_user_id'],
                'resolved_date' => $resolvedAt,
                'resolution_description' => $validated['resolution_description'],
                'issue_status' => (int) $validated['status_id'],
                'parent_event_log_id' => $this->issue->id,
            ]);

            $this->issue->fill([
                'resolved_date' => $resolvedAt,
                'resolution_description' => $validated['resolution_description'],
                'resolved_by_user_id' => (int) $validated['resolved_by_user_id'],
                'issue_status' => (int) $validated['status_id'],
            ]);
            $this->issue->save();

            TesterEventLog::create([
                'date' => now(),
                'description' => '[HISTORY] Added solution for issue #' . $this->issue->id,
                'tester_id' => $this->issue->tester_id,
                'event_type' => (int) $problemTypeId,
                'created_by_user_id' => Auth::id() ?? 1,
                'issue_status' => null,
                'resolution_description' => null,
                'resolved_date' => null,
                'resolved_by_user_id' => null,
                'parent_event_log_id' => $this->issue->id,
            ]);
        });

        $this->dispatch('saved');
        session()->flash('message', 'Solution saved successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function getIssueTypeLabelProperty(): string
    {
        return strtolower((string) ($this->issue->eventType?->name ?? 'problem'));
    }

    public function getIssueUserLabelProperty(): string
    {
        $user = $this->issue->createdBy;
        if (! $user) {
            return '-';
        }

        $label = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        return $label !== '' ? $label : (string) ($user->email ?? '-');
    }

    public function render()
    {
        return view('livewire.pages.issues.add-solution');
    }
}
