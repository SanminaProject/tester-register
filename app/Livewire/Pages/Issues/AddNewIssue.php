<?php

namespace App\Livewire\Pages\Issues;

use App\Models\EventType;
use App\Models\IssueStatus;
use App\Models\Tester;
use App\Models\TesterEventLog;
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
    public string $solution = '';
    public ?int $status_id = null;

    public $testers = [];
    public $statuses = [];

    public function mount($issueId = null): void
    {
        $this->testers = Tester::query()->select('id', 'name')->orderBy('id')->get();
        $this->statuses = IssueStatus::query()->select('id', 'name')->orderBy('id')->get();
        $this->date = now()->toDateString();

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
        $this->solution = $issue->resolution_description ?? '';
        $this->status_id = $issue->issue_status;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'tester_id' => ['required', 'integer', 'exists:testers,id'],
            'problem' => ['required', 'string', 'max:1000'],
            'solution' => ['nullable', 'string', 'max:1000'],
            'status_id' => ['required', 'integer', 'exists:issue_statuses,id'],
        ]);

        $eventTypeId = EventType::query()
            ->whereRaw('LOWER(name) = ?', ['issue'])
            ->value('id');

        if (! $eventTypeId) {
            $this->addError('status_id', 'Issue event type is not configured.');
            return;
        }

        $statusName = (string) IssueStatus::query()->where('id', $validated['status_id'])->value('name');
        $isActive = strtolower($statusName) === 'active';
        $actorId = Auth::id() ?? 1;

        if ($this->isEdit && $this->issueId) {
            $issue = TesterEventLog::query()->activeIssueRows()->findOrFail($this->issueId);

            $original = [
                'date' => optional($issue->date)->toDateString(),
                'tester_id' => (int) $issue->tester_id,
                'problem' => (string) $issue->description,
                'solution' => (string) ($issue->resolution_description ?? ''),
                'status_id' => (int) ($issue->issue_status ?? 0),
            ];

            $issue->fill([
                'date' => Carbon::parse($validated['date'])->endOfDay(),
                'tester_id' => $validated['tester_id'],
                'description' => $validated['problem'],
                'resolution_description' => $validated['solution'] !== '' ? $validated['solution'] : null,
                'issue_status' => $validated['status_id'],
                'resolved_date' => $isActive ? null : now(),
                'resolved_by_user_id' => $isActive ? null : $actorId,
            ]);
            $issue->save();

            $changes = [];
            $current = [
                'date' => Carbon::parse($validated['date'])->toDateString(),
                'tester_id' => (int) $validated['tester_id'],
                'problem' => (string) $validated['problem'],
                'solution' => (string) ($validated['solution'] ?? ''),
                'status_id' => (int) $validated['status_id'],
            ];

            foreach ($current as $key => $value) {
                if (($original[$key] ?? null) !== $value) {
                    $changes[] = $key . ': [' . ($original[$key] ?? 'empty') . '] -> [' . $value . ']';
                }
            }

            if ($changes !== []) {
                $this->writeHistoryLog(
                    testerId: (int) $issue->tester_id,
                    eventTypeId: (int) $eventTypeId,
                    actorId: (int) $actorId,
                    message: '[HISTORY] Updated issue #' . $issue->id . ' | ' . implode('; ', $changes)
                );
            }

            $this->dispatch('saved');
            session()->flash('message', 'Issue updated successfully.');
            $this->dispatch('switchTab', tab: 'details', id: $issue->id);

            return;
        }

        $issue = TesterEventLog::create([
            'date' => Carbon::parse($validated['date'])->endOfDay(),
            'description' => $validated['problem'],
            'tester_id' => $validated['tester_id'],
            'event_type' => $eventTypeId,
            'created_by_user_id' => $actorId,
            'resolution_description' => $validated['solution'] !== '' ? $validated['solution'] : null,
            'issue_status' => $validated['status_id'],
            'resolved_date' => $isActive ? null : now(),
            'resolved_by_user_id' => $isActive ? null : $actorId,
        ]);

        $this->writeHistoryLog(
            testerId: (int) $issue->tester_id,
            eventTypeId: (int) $eventTypeId,
            actorId: (int) $actorId,
            message: '[HISTORY] Created issue #' . $issue->id
        );

        $this->dispatch('saved');
        session()->flash('message', 'Issue created successfully.');

        $this->reset(['tester_id', 'problem', 'solution', 'status_id']);
        $this->date = now()->toDateString();

        $this->dispatch('switchTab', tab: 'all');
    }

    private function writeHistoryLog(int $testerId, int $eventTypeId, int $actorId, string $message): void
    {
        TesterEventLog::create([
            'date' => now(),
            'description' => $message,
            'tester_id' => $testerId,
            'event_type' => $eventTypeId,
            'created_by_user_id' => $actorId,
            'issue_status' => null,
            'resolution_description' => null,
            'resolved_date' => null,
            'resolved_by_user_id' => null,
        ]);
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
