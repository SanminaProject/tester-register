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
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IssueWorkbench extends Component
{
    use WithPagination;

    public string $mode = 'active';
    public ?int $selectedIssueId = null;
    public bool $showInlineForm = false;

    public string $search = '';
    public array $columnFilters = [];

    /** @var array<string, array{column:string,label:string,stateKey:string,type:string,options:array}> */
    public array $filters = [];

    /** @var array<string, string> */
    public array $headers = [
        'id' => 'Log ID',
        'date' => 'Date',
        'tester_id' => 'Tester ID',
        'eventType.name' => 'Type',
        'description' => 'Description',
        'createdBy.email' => 'User',
        'issueStatusRelation.name' => 'Status',
    ];

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
                $label = $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

                if ($label === '') {
                    $label = (string) $user->email;
                }

                return [
                    'id' => (int) $user->id,
                    'name' => $label,
                ];
            })
            ->toArray();

        $this->filters = $this->buildFiltersConfig();
        $this->normalizeColumnFilters();
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

    public function updatedColumnFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->columnFilters = [];
        $this->normalizeColumnFilters();
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
            'add_issue' => 'Add New Issue',
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
        return $this->buildFilteredQuery()->paginate(10);
    }

    public function getUserLabelByIdProperty(): array
    {
        $labels = [];

        foreach ($this->users as $user) {
            $labels[(int) ($user['id'] ?? 0)] = (string) ($user['name'] ?? '-');
        }

        return $labels;
    }

    public function exportCurrentList()
    {
        $rows = $this->buildFilteredQuery()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnIndex = 1;
        foreach ($this->headers as $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', $label);
            $columnIndex++;
        }

        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $columnIndex = 1;
            foreach (array_keys($this->headers) as $key) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex) . $rowNumber,
                    (string) (data_get($row, $key) ?? '-')
                );
                $columnIndex++;
            }
            $rowNumber++;
        }

        foreach (range(1, count($this->headers)) as $columnNumber) {
            $sheet->getColumnDimensionByColumn($columnNumber)->setAutoSize(true);
        }

        $fileName = 'active-issues-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function buildFiltersConfig(): array
    {
        $definitions = [];

        foreach ($this->headers as $column => $label) {
            $type = $this->resolveFilterType($column);
            $definitions[$column] = [
                'column' => $column,
                'label' => $label,
                'stateKey' => 'filter_' . substr(md5($column), 0, 12),
                'type' => $type,
                'options' => $type === 'multi' ? $this->getFilterOptions($column) : [],
            ];
        }

        return $definitions;
    }

    protected function resolveFilterType(string $column): string
    {
        if ($column === 'id' || $column === 'tester_id') {
            return 'range';
        }

        if ($column === 'date') {
            return 'date_range';
        }

        if (in_array($column, ['eventType.name', 'createdBy.email', 'issueStatusRelation.name'], true)) {
            return 'multi';
        }

        return 'text';
    }

    protected function getFilterOptions(string $column): array
    {
        if (! str_contains($column, '.')) {
            return TesterEventLog::query()
                ->whereNotNull($column)
                ->distinct()
                ->orderBy($column)
                ->pluck($column)
                ->filter()
                ->values()
                ->all();
        }

        [$relation, $relatedColumn] = explode('.', $column, 2);
        $model = new TesterEventLog();

        if (! method_exists($model, $relation)) {
            return [];
        }

        $relatedModel = $model->{$relation}()->getRelated();

        return $relatedModel::query()
            ->whereNotNull($relatedColumn)
            ->distinct()
            ->orderBy($relatedColumn)
            ->pluck($relatedColumn)
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeColumnFilters(): void
    {
        foreach ($this->filters as $definition) {
            $stateKey = $definition['stateKey'];
            $type = $definition['type'];
            $current = $this->columnFilters[$stateKey] ?? null;

            if ($type === 'multi') {
                $this->columnFilters[$stateKey] = is_array($current)
                    ? array_values(array_filter($current, fn ($item) => $item !== null && $item !== ''))
                    : [];
                continue;
            }

            if ($type === 'range') {
                $this->columnFilters[$stateKey] = [
                    'min' => is_array($current) ? ($current['min'] ?? null) : null,
                    'max' => is_array($current) ? ($current['max'] ?? null) : null,
                ];
                continue;
            }

            if ($type === 'date_range') {
                $this->columnFilters[$stateKey] = [
                    'from' => is_array($current) ? ($current['from'] ?? null) : null,
                    'to' => is_array($current) ? ($current['to'] ?? null) : null,
                ];
                continue;
            }

            if (is_array($current)) {
                $this->columnFilters[$stateKey] = '';
            }
        }
    }

    protected function applyColumnFilters(Builder $query): Builder
    {
        foreach ($this->filters as $column => $definition) {
            $stateKey = $definition['stateKey'];
            $value = $this->columnFilters[$stateKey] ?? null;

            if ($definition['type'] === 'range') {
                $minValue = is_array($value) ? ($value['min'] ?? null) : null;
                $maxValue = is_array($value) ? ($value['max'] ?? null) : null;

                if ($minValue !== null && $minValue !== '') {
                    $query->where($column, '>=', $minValue);
                }

                if ($maxValue !== null && $maxValue !== '') {
                    $query->where($column, '<=', $maxValue);
                }

                continue;
            }

            if ($definition['type'] === 'date_range') {
                $from = is_array($value) ? ($value['from'] ?? null) : null;
                $to = is_array($value) ? ($value['to'] ?? null) : null;

                if ($from !== null && $from !== '') {
                    $query->whereDate($column, '>=', $from);
                }

                if ($to !== null && $to !== '') {
                    $query->whereDate($column, '<=', $to);
                }

                continue;
            }

            if ($definition['type'] === 'multi') {
                if (! is_array($value)) {
                    continue;
                }

                $selectedValues = array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
                if (empty($selectedValues)) {
                    continue;
                }

                if (str_contains($column, '.')) {
                    [$relation, $relColumn] = explode('.', $column, 2);
                    $query->whereHas($relation, function (Builder $relationQuery) use ($relColumn, $selectedValues) {
                        $relationQuery->whereIn($relColumn, $selectedValues);
                    });
                } else {
                    $query->whereIn($column, $selectedValues);
                }

                continue;
            }

            $keyword = trim((string) $value);
            if ($keyword === '') {
                continue;
            }

            if (str_contains($column, '.')) {
                [$relation, $relColumn] = explode('.', $column, 2);
                $query->whereHas($relation, function (Builder $relationQuery) use ($relColumn, $keyword) {
                    $relationQuery->where($relColumn, 'like', '%' . $keyword . '%');
                });
            } else {
                $query->where($column, 'like', '%' . $keyword . '%');
            }
        }

        return $query;
    }

    protected function buildFilteredQuery(): Builder
    {
        $query = TesterEventLog::query()
            ->with(['eventType', 'createdBy', 'issueStatusRelation'])
            ->activeIssueRows()
            ->orderByDesc('date');

        $query = $this->applyColumnFilters($query);

        $keyword = trim($this->search);
        if ($keyword !== '') {
            $searchColumns = array_keys($this->headers);

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

        return $query;
    }

    private function saveIssue(): void
    {
        $validated = $this->validate([
            'issueForm.date' => ['required', 'date_format:Y-m-d\\TH:i'],
            'issueForm.tester_id' => ['required', 'integer', 'exists:testers,id'],
            'issueForm.description' => ['required', 'string', 'max:2000'],
            'issueForm.created_by_user_id' => ['required', 'integer', 'exists:users,id'],
            'issueForm.status_id' => ['required', 'integer', 'exists:issue_statuses,id'],
        ]);

        $eventTypeId = TesterEventLog::resolveEventTypeId('problem')
            ?? TesterEventLog::resolveEventTypeId('issue');

        if (! $eventTypeId) {
            $this->addError('issueForm.status_id', 'Issue event type is not configured.');
            return;
        }

        $selectedStatusId = isset($validated['issueForm']['status_id'])
            ? (int) $validated['issueForm']['status_id']
            : null;

        if ($selectedStatusId === null) {
            $this->addError('issueForm.status_id', 'Issue status is required.');
            return;
        }

        $issuePayload = $validated['issueForm'];

        TesterEventLog::create([
            'date' => Carbon::createFromFormat('Y-m-d\\TH:i', (string) $issuePayload['date']),
            'description' => (string) $issuePayload['description'],
            'tester_id' => (int) $issuePayload['tester_id'],
            'event_type' => (int) $eventTypeId,
            'created_by_user_id' => (int) $issuePayload['created_by_user_id'],
            'issue_status' => $selectedStatusId,
            'resolved_date' => null,
            'resolved_by_user_id' => null,
            'resolution_description' => null,
            'parent_event_log_id' => null,
        ]);

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
            'solutionForm.resolution_date' => ['required', 'date_format:Y-m-d\\TH:i'],
            'solutionForm.resolution_description' => ['required', 'string', 'max:2000'],
            'solutionForm.resolved_by_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $solutionTypeId = TesterEventLog::resolveEventTypeId('solution');
        $solvedStatusId = $this->resolveIssueStatusId('solved');

        if ($solutionTypeId === null) {
            $this->addError('solutionForm.status_id', 'Event types problem/solution are not configured.');
            return;
        }

        if ($solvedStatusId === null) {
            $this->addError('solutionForm.status_id', 'Issue status Solved is not configured.');
            return;
        }

        $issue = TesterEventLog::query()->activeIssueRows()->findOrFail($this->selectedIssueId);
        $solutionPayload = $validated['solutionForm'];

        DB::transaction(function () use ($issue, $solutionPayload, $solutionTypeId, $solvedStatusId) {
            $resolvedAt = Carbon::createFromFormat('Y-m-d\\TH:i', (string) $solutionPayload['resolution_date']);

            TesterEventLog::create([
                'date' => $resolvedAt,
                'description' => (string) $solutionPayload['resolution_description'],
                'tester_id' => (int) $issue->tester_id,
                'event_type' => (int) $solutionTypeId,
                'created_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'resolved_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'resolved_date' => $resolvedAt,
                'resolution_description' => (string) $solutionPayload['resolution_description'],
                'issue_status' => $solvedStatusId,
                'parent_event_log_id' => (int) $issue->id,
            ]);

            $issue->fill([
                'resolved_date' => $resolvedAt,
                'resolution_description' => (string) $solutionPayload['resolution_description'],
                'resolved_by_user_id' => (int) $solutionPayload['resolved_by_user_id'],
                'issue_status' => $solvedStatusId,
            ]);
            $issue->save();
        });

        session()->flash('message', 'Solution saved successfully.');
        $this->cancelInlineForm();
    }

    private function resetIssueForm(): void
    {
        $this->issueForm = [
            'date' => now()->format('Y-m-d\\TH:i'),
            'tester_id' => null,
            'description' => '',
            'created_by_user_id' => Auth::id() ?? 1,
            'status_id' => $this->resolveIssueStatusId('active'),
        ];
    }

    private function resetSolutionForm(): void
    {
        $this->solutionForm = [
            'resolution_date' => now()->format('Y-m-d\\TH:i'),
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
        $this->filters = $this->buildFiltersConfig();
        $this->normalizeColumnFilters();

        return view('livewire.pages.issues.issue-workbench');
    }
}
