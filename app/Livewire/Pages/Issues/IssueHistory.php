<?php

namespace App\Livewire\Pages\Issues;

use App\Models\TesterEventLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IssueHistory extends Component
{
    use WithPagination;

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

    public function mount(): void
    {
        $this->filters = $this->buildFiltersConfig();
        $this->normalizeColumnFilters();
    }

    protected function getPageName(): string
    {
        return 'issue-history-page';
    }

    public function updatingSearch(): void
    {
        $this->resetPage($this->getPageName());
    }

    public function updatedColumnFilters(): void
    {
        $this->resetPage($this->getPageName());
    }

    public function clearFilters(): void
    {
        $this->columnFilters = [];
        $this->normalizeColumnFilters();
        $this->resetPage($this->getPageName());
    }

    public function beginAddIssue(): void
    {
        $this->dispatch('switchTab', tab: 'add');
    }

    public function exportCurrentList()
    {
        $issues = $this->buildGroupsQuery()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnIndex = 1;
        foreach ($this->headers as $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', $label);
            $columnIndex++;
        }

        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($issues as $issue) {
            $sheet->setCellValue('A' . $rowNumber, (string) $issue->id);
            $sheet->setCellValue('B' . $rowNumber, (string) ($issue->date?->format('Y-m-d H:i:s') ?? '-'));
            $sheet->setCellValue('C' . $rowNumber, (string) ($issue->tester_id ?? '-'));
            $sheet->setCellValue('D' . $rowNumber, 'Problem');
            $sheet->setCellValue('E' . $rowNumber, (string) ($issue->description ?? '-'));
            $sheet->setCellValue('F' . $rowNumber, (string) ($issue->createdBy?->full_name ?? $issue->createdBy?->email ?? '-'));
            $sheet->setCellValue('G' . $rowNumber, strtoupper((string) ($issue->issueStatusRelation?->name ?? '-')));
            $rowNumber++;

            foreach ($issue->solutionEntries as $solution) {
                $sheet->setCellValue('A' . $rowNumber, '');
                $sheet->setCellValue('B' . $rowNumber, (string) ($solution->date?->format('Y-m-d H:i:s') ?? '-'));
                $sheet->setCellValue('C' . $rowNumber, '');
                $sheet->setCellValue('D' . $rowNumber, 'Solution');
                $sheet->setCellValue('E' . $rowNumber, (string) ($solution->resolution_description ?? $solution->description ?? '-'));
                $sheet->setCellValue('F' . $rowNumber, (string) ($solution->resolvedBy?->full_name ?? $solution->resolvedBy?->email ?? $solution->createdBy?->full_name ?? '-'));
                $sheet->setCellValue('G' . $rowNumber, '');
                $rowNumber++;
            }
        }

        foreach (range(1, count($this->headers)) as $columnNumber) {
            $sheet->getColumnDimensionByColumn($columnNumber)->setAutoSize(true);
        }

        $fileName = 'issue-history-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function getGroupsProperty()
    {
        return $this->buildGroupsQuery()->paginate(10, ['*'], $this->getPageName());
    }

    protected function buildGroupsQuery(): Builder
    {
        $query = TesterEventLog::query()
            ->with([
                'tester',
                'createdBy',
                'resolvedBy',
                'issueStatusRelation',
                'eventType',
                'solutionEntries' => function ($builder): void {
                    $builder->solutions()
                        ->where('description', 'not like', '[HISTORY]%')
                        ->with(['createdBy', 'resolvedBy', 'issueStatusRelation', 'eventType'])
                        ->orderBy('date');
                },
            ])
            ->problems()
            ->whereNull('parent_event_log_id')
            ->where('description', 'not like', '[HISTORY]%')
            ->orderByDesc('id');

        $query = $this->applyColumnFilters($query);

        $keyword = trim($this->search);
        if ($keyword !== '') {
            $searchColumns = [
                ...array_keys($this->headers),
                'createdBy.first_name',
                'createdBy.last_name',
                'resolvedBy.first_name',
                'resolvedBy.last_name',
                'solutionEntries.description',
                'solutionEntries.resolution_description',
            ];

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

    public function render()
    {
        $this->filters = $this->buildFiltersConfig();
        $this->normalizeColumnFilters();

        return view('livewire.pages.issues.issue-history');
    }
}
