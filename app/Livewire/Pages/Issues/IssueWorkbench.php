<?php

namespace App\Livewire\Pages\Issues;

use App\Models\IssueStatus;
use App\Models\Tester;
use App\Models\TesterEventLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class IssueWorkbench extends Component
{
    use WithPagination;

    public string $mode = 'active';
    public ?int $selectedIssueId = null;
    public bool $showInlineForm = false;

    public string $search = '';

    /** @var array<string, string> */
    public array $filters = [
        'id' => 'Log ID',
        'date' => 'Date',
        'tester_id' => 'Tester ID',
        'eventType.name' => 'Type',
        'description' => 'Description',
        'createdBy.email' => 'User',
        'issueStatusRelation.name' => 'Status',
    ];

    /** @var list<string> */
    public array $activeFilters = [];

    /** @var array{date:string,tester_id:int|null,description:string,created_by_user_id:int|null,status_id:int|null} */
    public array $issueForm = [
        'date' => '',
        'tester_id' => null,
        'description' => '',
        'created_by_user_id' => null,
        'status_id' => null,
    ];

    /** @var array{resolution_date:string,resolution_description:string,resolved_by_user_id:int|null,status_id:int|null} */
    public array $solutionForm = [
        'resolution_date' => '',
        'resolution_description' => '',
        'resolved_by_user_id' => null,
        'status_id' => null,
    ];

    public $testers = [];
    public $users = [];
    public $statuses = [];

    public function mount(string $requestedTab = 'all', ?int $requestedIssueId = null): void
    {
        $this->testers = Tester::query()->select('id', 'name')->orderBy('id')->get();
        $this->statuses = IssueStatus::query()->select('id', 'name')->orderBy('id')->get();
        $this->users = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function (User $user) {
                $label = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

                if ($label === '') {
                    $label = (string) $user->email;
                }

                return [
                    'id' => (int) $user->id,
                    'name' => $label,
                ];
            })
            ->toArray();

        $this->resetIssueForm();
        $this->resetSolutionForm();
        $this->applyRequestedTab($requestedTab, $requestedIssueId);
    }

    #[On('issue-mode-requested')]
    public function onIssueModeRequested($tab = 'all', $id = null): void
    {
        if (is_array($tab)) {
            $resolvedTab = (string) ($tab['tab'] ?? 'all');
            $resolvedId = isset($tab['id']) ? (int) $tab['id'] : null;
            $this->applyRequestedTab($resolvedTab, $resolvedId);

            return;
        }

        $this->applyRequestedTab((string) $tab, $id !== null ? (int) $id : null);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleFilter(string $filter): void
    {
        if (in_array($filter, $this->activeFilters, true)) {
            $this->activeFilters = array_values(array_diff($this->activeFilters, [$filter]));
        } else {
            $this->activeFilters[] = $filter;
        }

        $this->resetPage();
    }

    public function beginAddIssue(): void
    {
        $this->mode = 'add_issue';
        $this->selectedIssueId = null;
        $this->showInlineForm = true;
        $this->resetValidation();
        $this->resetIssueForm();
        $this->dispatch('switchTab', tab: 'add');
    }

    public function beginAddSolution(int $issueId): void
    {
        $issue = TesterEventLog::query()->activeIssueRows()->findOrFail($issueId);

        $this->mode = 'add_solution';
        $this->selectedIssueId = $issue->id;
        $this->showInlineForm = true;
        $this->resetValidation();
        $this->resetSolutionForm();
        $this->dispatch('switchTab', tab: 'solution', id: $issue->id);
    }

    public function cancelInlineForm(): void
    {
        $this->mode = 'active';
        $this->selectedIssueId = null;
        $this->showInlineForm = false;
        $this->resetValidation();
        $this->resetIssueForm();
        $this->resetSolutionForm();
        $this->dispatch('switchTab', tab: 'all');
    }

    public function save(): void
    {
        if (! $this->showInlineForm) {
            return;
        }

        if ($this->mode === 'add_issue') {
            $this->saveIssue();
            return;
        }

        if ($this->mode === 'add_solution') {
            $this->saveSolution();
        }
    }

    public function getHeaderTitleProperty(): string
    {
        return match ($this->mode) {
            'add_issue' => 'Add Issue',
            'add_solution' => 'Add Solution',
            default => 'Active Issues',
        };
    }

    public function getMainButtonLabelProperty(): string
    {
        return $this->mode === 'active' ? 'Add Issue' : 'Save';
    }

    public function getMainButtonActionProperty(): string
    {
        return $this->mode === 'active' ? 'beginAddIssue' : 'save';
    }

    public function getRowsProperty()
    {
        $query = TesterEventLog::query()
            ->with(['eventType', 'createdBy', 'issueStatusRelation'])
            ->activeIssueRows()
            ->orderByDesc('date');

        $searchColumns = [
            'id',
            'date',
            'tester_id',
            'eventType.name',
            'description',
            'createdBy.email',
            'issueStatusRelation.name',
        ];

        if ($this->activeFilters !== []) {
            $searchColumns = array_values(array_intersect($searchColumns, $this->activeFilters));
        }

        $keyword = trim($this->search);
        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        $builder->orWhereHas($relation, function (Builder $relationQuery) use ($relColumn, $keyword) {
                            $relationQuery->where($relColumn, 'like', '%' . $keyword . '%');
                        });
                    } else {
                        $builder->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            });
        }

        return $query->paginate(10);
    }

    public function getUserLabelByIdProperty(): array
    {
        $labels = [];

        foreach ($this->users as $user) {
            $labels[(int) ($user['id'] ?? 0)] = (string) ($user['name'] ?? '-');
        }

        return $labels;
    }

    private function saveIssue(): void
    {
        $validated = $this->validate([
            'issueForm.date' => ['required', 'date'],
            'issueForm.tester_id' => ['required', 'integer', 'exists:testers,id'],
            'issueForm.description' => ['required', 'string', 'max:1000'],
            'issueForm.created_by_user_id' => ['required', 'integer', 'exists:users,id'],
            'issueForm.status_id' => ['required', 'integer', 'exists:issue_statuses,id'],
        ]);

        $eventTypeId = TesterEventLog::resolveEventTypeId('problem')
            ?? TesterEventLog::resolveEventTypeId('issue');

        if (! $eventTypeId) {
            $this->addError('issueForm.status_id', 'Issue event type is not configured.');
            return;
        }

        $issuePayload = $validated['issueForm'];

        $issue = TesterEventLog::create([
            'date' => Carbon::parse((string) $issuePayload['date'])->endOfDay(),
            'description' => (string) $issuePayload['description'],
            'tester_id' => (int) $issuePayload['tester_id'],
            'event_type' => (int) $eventTypeId,
            'created_by_user_id' => (int) $issuePayload['created_by_user_id'],
            'issue_status' => (int) $issuePayload['status_id'],
            'resolved_date' => null,
            'resolved_by_user_id' => null,
            'resolution_description' => null,
            'parent_event_log_id' => null,
        ]);

        $this->writeHistoryLog(
            testerId: (int) $issue->tester_id,
            eventTypeId: (int) $eventTypeId,
            actorId: (int) (Auth::id() ?? 1),
            message: '[HISTORY] Created issue #' . $issue->id
        );

        session()->flash('message', 'Issue created successfully.');
        $this->cancelInlineForm();
    }

    private function saveSolution(): void
    {
        if (! $this->selectedIssueId) {
            $this->addError('solutionForm.resolution_date', 'No issue selected for solution.');
            return;
        }

        $validated = $this->validate([
            'solutionForm.resolution_date' => ['required', 'date'],
            'solutionForm.resolution_description' => ['required', 'string', 'max:1000'],
            'solutionForm.resolved_by_user_id' => ['required', 'integer', 'exists:users,id'],
            'solutionForm.status_id' => ['required', 'integer', 'exists:issue_statuses,id'],
        ]);

        $solutionTypeId = TesterEventLog::resolveEventTypeId('solution');
        $problemTypeId = TesterEventLog::resolveEventTypeId('problem')
            ?? TesterEventLog::resolveEventTypeId('issue');

        if ($solutionTypeId === null || $problemTypeId === null) {
            $this->addError('solutionForm.status_id', 'Event types problem/solution are not configured.');
            return;
        }

        $issue = TesterEventLog::query()->activeIssueRows()->findOrFail($this->selectedIssueId);
        $solutionPayload = $validated['solutionForm'];

        DB::transaction(function () use ($issue, $solutionPayload, $solutionTypeId, $problemTypeId) {
            $resolvedAt = Carbon::parse((string) $solutionPayload['resolution_date'])->endOfDay();

            TesterEventLog::create([
                'date' => $resolvedAt,
                'description' => (string) $solutionPayload['resolution_description'],
                'tester_id' => (int) $issue->tester_id,
                'event_type' => (int) $solutionTypeId,
                'created_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'resolved_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'resolved_date' => $resolvedAt,
                'resolution_description' => (string) $solutionPayload['resolution_description'],
                'issue_status' => (int) $solutionPayload['status_id'],
                'parent_event_log_id' => (int) $issue->id,
            ]);

            $issue->fill([
                'resolved_date' => $resolvedAt,
                'resolution_description' => (string) $solutionPayload['resolution_description'],
                'resolved_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'issue_status' => (int) $solutionPayload['status_id'],
            ]);
            $issue->save();

            TesterEventLog::create([
                'date' => now(),
                'description' => '[HISTORY] Added solution for issue #' . $issue->id,
                'tester_id' => (int) $issue->tester_id,
                'event_type' => (int) $problemTypeId,
                'created_by_user_id' => (int) (Auth::id() ?? 1),
                'issue_status' => null,
                'resolution_description' => null,
                'resolved_date' => null,
                'resolved_by_user_id' => null,
                'parent_event_log_id' => (int) $issue->id,
            ]);
        });

        session()->flash('message', 'Solution saved successfully.');
        $this->cancelInlineForm();
    }

    private function resetIssueForm(): void
    {
        $this->issueForm = [
            'date' => now()->toDateString(),
            'tester_id' => null,
            'description' => '',
            'created_by_user_id' => Auth::id() ?? 1,
            'status_id' => $this->resolveIssueStatusId('active'),
        ];
    }

    private function resetSolutionForm(): void
    {
        $this->solutionForm = [
            'resolution_date' => now()->toDateString(),
            'resolution_description' => '',
            'resolved_by_user_id' => Auth::id() ?? 1,
            'status_id' => $this->resolveIssueStatusId('solved'),
        ];
    }

    private function resolveIssueStatusId(string $statusName): ?int
    {
        $statusId = IssueStatus::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($statusName)])
            ->value('id');

        return $statusId !== null ? (int) $statusId : null;
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

    private function applyRequestedTab(string $requestedTab, ?int $requestedIssueId = null): void
    {
        if ($requestedTab === 'add') {
            $this->mode = 'add_issue';
            $this->selectedIssueId = null;
            $this->showInlineForm = true;
            $this->resetValidation();
            $this->resetIssueForm();

            return;
        }

        if ($requestedTab === 'solution') {
            $candidateId = $requestedIssueId;

            if (! $candidateId) {
                $candidateId = (int) (TesterEventLog::query()
                    ->activeIssueRows()
                    ->orderByDesc('date')
                    ->value('id') ?? 0);
            }

            if ($candidateId > 0) {
                $this->mode = 'add_solution';
                $this->selectedIssueId = $candidateId;
                $this->showInlineForm = true;
                $this->resetValidation();
                $this->resetSolutionForm();

                return;
            }
        }

        $this->mode = 'active';
        $this->selectedIssueId = null;
        $this->showInlineForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-workbench');
    }
}
