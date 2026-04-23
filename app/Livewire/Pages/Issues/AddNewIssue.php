<?php

namespace App\Livewire\Pages\Issues;

use App\Models\IssueStatus;
use App\Models\Tester;
use App\Models\TesterEventLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddNewIssue extends Component
{
    public ?int $issueId = null;
    public bool $isEdit = false;

    public string $date = '';
    public ?int $tester_id = null;
    public string $problem = '';
    public ?int $created_by_user_id = null;
    public ?int $status_id = null;
    public string $type = 'problem';

    public $testers = [];
    public $statuses = [];
    public $users = [];

    public function mount($issueId = null): void
    {
        $this->testers = Tester::query()->select('id', 'name')->orderBy('id')->get();
        $this->statuses = IssueStatus::query()
            ->select('id', 'name')
            ->whereRaw('LOWER(name) in (?, ?)', ['active', 'solved'])
            ->orderBy('id')
            ->get();
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
        $this->date = now()->toDateString();
        $this->created_by_user_id = Auth::id() ?? 1;

        $defaultStatusId = (int) (IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', ['active'])
            ->value('id') ?? 0);

        if ($defaultStatusId > 0) {
            $this->status_id = $defaultStatusId;
        }

        if (! $issueId) {
            return;
        }

        $this->issueId = (int) $issueId;
        $this->isEdit = true;

        $issue = TesterEventLog::query()
            ->activeIssueRows()
            ->findOrFail($this->issueId);

        $this->date = optional($issue->date)->toDateString() ?? now()->toDateString();
        $this->tester_id = $issue->tester_id;
        $this->problem = $issue->description;
        $this->created_by_user_id = $issue->created_by_user_id;
        $this->status_id = $issue->issue_status;
        $this->type = strtolower((string) ($issue->eventType?->name ?? 'problem'));
    }

    public function save(): void
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'tester_id' => ['required', 'integer', 'exists:testers,id'],
            'problem' => ['required', 'string', 'max:1000'],
            'created_by_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $eventTypeId = TesterEventLog::resolveEventTypeId('problem')
            ?? TesterEventLog::resolveEventTypeId('issue');

        if (! $eventTypeId) {
            $this->addError('status_id', 'Issue event type is not configured.');
            return;
        }

        $activeStatusId = $this->resolveIssueStatusId('active');
        if ($activeStatusId === null) {
            $this->addError('status_id', 'Issue status Active is not configured.');
            return;
        }

        if ($this->isEdit && $this->issueId) {
            $issue = TesterEventLog::query()->activeIssueRows()->findOrFail($this->issueId);

            $issue->fill([
                'date' => Carbon::parse($validated['date'])->startOfDay(),
                'tester_id' => $validated['tester_id'],
                'description' => $validated['problem'],
                'created_by_user_id' => $validated['created_by_user_id'],
                'issue_status' => $activeStatusId,
            ]);
            $issue->save();

            $this->dispatch('saved');
            session()->flash('message', 'Issue updated successfully.');
            $this->dispatch('switchTab', tab: 'all');

            return;
        }

        TesterEventLog::create([
            'date' => Carbon::parse($validated['date'])->startOfDay(),
            'description' => $validated['problem'],
            'tester_id' => $validated['tester_id'],
            'event_type' => $eventTypeId,
            'created_by_user_id' => $validated['created_by_user_id'],
            'issue_status' => $activeStatusId,
            'resolved_date' => null,
            'resolved_by_user_id' => null,
            'resolution_description' => null,
        ]);

        $this->dispatch('saved');
        session()->flash('message', 'Issue created successfully.');

        $this->reset(['tester_id', 'problem']);
        $this->date = now()->toDateString();
        $this->created_by_user_id = Auth::id() ?? 1;

        $defaultStatusId = (int) (IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', ['active'])
            ->value('id') ?? 0);

        if ($defaultStatusId > 0) {
            $this->status_id = $defaultStatusId;
        }

        $this->dispatch('switchTab', tab: 'all');
    }

    private function resolveIssueStatusId(string $statusName): ?int
    {
        $statusId = IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($statusName)])
            ->value('id');

        return $statusId !== null ? (int) $statusId : null;
    }

    public function getCurrentUserLabelProperty(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'Guest';
        }

        $name = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        return $name !== '' ? $name : (string) ($user->email ?? 'User #' . $user->id);
    }

    public function render()
    {
        return view('livewire.pages.issues.add-new-issue');
    }
}
