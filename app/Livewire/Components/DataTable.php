<?php

namespace App\Livewire\Components;

use App\Models\DataChangeLog;
use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterEventLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use App\Models\TesterSparePart;
use App\Models\TesterSparePartSupplier;

// TODO: clean up (especially role part)

class DataTable extends Component
{
    use WithPagination, WithoutUrlPagination;

    public bool $goToLastPageOnRender = false;

    public array $headers = [];
    public string $type = 'testers';

    public string $search = '';
    public array $columnFilters = [];

    public string $title = 'Data List';
    public string $searchPlaceholder = 'Search...';
    public string $addButtonLabel = 'Add';
    public bool $showAddButton = true;

    public array $filters = [];

    public function mount(): void
    {
        if ($this->type === 'testers' && session()->pull('go_to_testers_last_page', false)) {
            $this->goToLastPageOnRender = true;
        }
    }

    public function getHasDetailsProperty(): bool
    {
        $plural = Str::plural($this->type);
        $singular = Str::singular($this->type);

        if (view()->exists("livewire.pages.admin.{$plural}.{$singular}-details") || view()->exists("livewire.pages.admin.{$singular}.{$singular}-details")) {
            return true;
        } 

        if (view()->exists("livewire.pages.inventory.{$plural}.{$singular}-details") || view()->exists("livewire.pages.inventory.{$singular}.{$singular}-details")) {
            return true;
        } 
        
        return view()->exists("livewire.pages.{$plural}.{$singular}-details");
    }

    protected function getModelClass(): string
    {
        return match ($this->type) {
            'testers' => Tester::class,
            'fixtures' => Fixture::class,
            'personnel' => User::class,
            'roles' => Role::class,
            'spare-parts' => TesterSparePart::class,
            'suppliers' => TesterSparePartSupplier::class,
            'fixture-audit-logs' => DataChangeLog::class,
            'tester-audit-logs' => DataChangeLog::class,
            'inventory-audit-logs' => DataChangeLog::class,
            'issues' => TesterEventLog::class,
            'issue-history' => TesterEventLog::class,
            default => throw new \Exception('Invalid data type'),
        };
    }

    protected function getRelations(): array
    {
        return match ($this->type) {
            'testers' => ['owner', 'statusRelation', 'location'],
            'fixtures' => ['tester', 'location', 'status'],
            'personnel' => ['roles', 'testers'], 
            'spare-parts' => ['tester', 'supplier'],
            'fixture-audit-logs' => ['fixture', 'user'],
            'tester-audit-logs' => ['tester', 'user'],
            'inventory-audit-logs' => ['spare_part', 'spare_part_supplier', 'user'],
            'issues' => ['tester', 'createdBy', 'issueStatusRelation', 'eventType'],
            'issue-history' => ['tester', 'createdBy', 'issueStatusRelation', 'eventType'],
            default => [],
        };
    }

    protected function getSearchColumns(): array
    {
        if (! empty($this->headers)) {
            return array_keys($this->headers);
        }

        return match ($this->type) {
            'testers' => ['name', 'description', 'operating_system'],
            'fixtures' => ['name', 'description', 'manufacturer'],
            'personnel' => ['first_name', 'last_name', 'email'],
            'roles' => ['name', 'guard_name', 'supplier.supplier_name', 'tester.name'],
            'spare-parts' => ['name', 'description'],
            'suppliers' => ['supplier_name', 'supplier_email'],
            'fixture-audit-logs' => ['explanation', 'fixture_id', 'fixture.name', 'user.email'],
            'tester-audit-logs' => ['explanation', 'tester_id', 'tester.name', 'user.email'],
            'inventory-audit-logs' => ['explanation', 'spare_part_id', 'spare_part.name', 'spare_part_supplier_id', 'spare_part_supplier.supplier_name', 'user.email'],
            'issues' => ['id', 'date', 'tester_id', 'eventType.name', 'description', 'createdBy.email', 'issueStatusRelation.name'],
            'issue-history' => ['id', 'date', 'tester_id', 'eventType.name', 'description', 'createdBy.email', 'issueStatusRelation.name'],
            default => [],
        };
    }

    protected function getFiltersConfig(): array
    {
        $definitions = [];

        foreach ($this->headers as $column => $label) {
            $filterType = $this->resolveFilterType($column);

            $definitions[$column] = [
                'column' => $column,
                'label' => $label,
                'stateKey' => $this->getFilterStateKey($column),
                'type' => $filterType,
                'options' => $filterType === 'multi' ? $this->getFilterOptions($column) : [],
            ];
        }

        return $definitions;
    }

    protected function getFilterStateKey(string $column): string
    {
        return 'filter_' . substr(md5($column), 0, 12);
    }

    protected function resolveFilterType(string $column): string
    {
        if ($column === 'needs_reorder') {
            return 'multi';
        }

        if ($column === 'id' || str_ends_with($column, '_id') || str_ends_with($column, '_count')) {
            return 'range';
        }

        if ($column === 'date' || str_contains($column, 'date') || str_ends_with($column, '_at')) {
            return 'date_range';
        }

        if ($this->isSelectableColumn($column)) {
            return 'multi';
        }

        return 'text';
    }

    protected function isSelectableColumn(string $column): bool
    {
        $selectableColumns = match ($this->type) {
            'testers' => ['product_family', 'owner.name', 'statusRelation.name', 'location.name', 'type', 'manufacturer', 'operating_system'],
            'fixtures' => ['manufacturer', 'tester.name', 'location.name', 'status.name'],
            'personnel' => ['roles.name'],
            'roles' => ['guard_name'],
            'fixture-audit-logs' => ['fixture.name', 'user.email'],
            'tester-audit-logs' => ['tester.name', 'user.email'],
            'spare-part-audit-logs' => ['sparePart.name', 'user.email'],
            'issues' => ['eventType.name', 'createdBy.email', 'issueStatusRelation.name'],
            'issue-history' => ['eventType.name', 'createdBy.email', 'issueStatusRelation.name'],
            default => [],
        };

        return in_array($column, $selectableColumns, true);
    }

    protected function getFilterOptions(string $column): array
    {
        $modelClass = $this->getModelClass();
        $model = new $modelClass();

        if ($column === 'needs_reorder') {
            return ['REORDER', 'IN STOCK'];
        }

        if (str_contains($column, '.')) {
            [$relation, $relatedColumn] = explode('.', $column, 2);

            if (! method_exists($model, $relation)) {
                return [];
            }

            $relationObject = $model->{$relation}();
            $relatedModel = $relationObject->getRelated();

            if ($this->isUserNameRelationColumn($relation, $relatedColumn)) {
                return $relatedModel::query()
                    ->selectRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as full_name")
                    ->where(function ($query) {
                        $query->whereNotNull('first_name')->orWhereNotNull('last_name');
                    })
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->pluck('full_name')
                    ->filter()
                    ->values()
                    ->all();
            }

            return $relatedModel::query()
                ->whereNotNull($relatedColumn)
                ->distinct()
                ->orderBy($relatedColumn)
                ->pluck($relatedColumn)
                ->filter()
                ->values()
                ->all();
        }

        return $modelClass::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values()
            ->all();
    }

    public function clearFilters(): void
    {
        $this->columnFilters = [];
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedColumnFilters(): void
    {
        $this->resetPage();
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

    protected function buildBaseQuery()
    {
        $model = $this->getModelClass();
        $query = $model::query();

        $relations = $this->getRelations();
        if (! empty($relations)) {
            $query->with($relations);
        }

        if ($this->type === 'roles') {
            $query->withCount('users');
        }

        if ($this->type === 'suppliers') {
            $query->withCount('spareParts');
        }

        $query = $this->applyTypeScopes($query);

        if (in_array($this->type, ['fixture-audit-logs', 'tester-audit-logs', 'inventory-audit-logs'])) {
            $query->orderByDesc('changed_at')->orderByDesc('id');
        }

        return $query;
    }

    protected function applyColumnFilters($query)
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

            if ($column === 'needs_reorder' && is_array($value)) {
                $selectedValues = array_values(array_filter($value));

                if (empty($selectedValues)) {
                    continue;
                }

                $query->where(function ($q) use ($selectedValues) {
                    if (in_array('REORDER', $selectedValues)) {
                        $q->orWhereColumn('quantity_in_stock', '<=', 'reorder_level');
                    }

                    if (in_array('IN STOCK', $selectedValues)) {
                        $q->orWhereColumn('quantity_in_stock', '>', 'reorder_level');
                    }
                });

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
                    [$relation, $relatedColumn] = explode('.', $column, 2);
                    $query->whereHas($relation, function ($relationQuery) use ($relation, $relatedColumn, $selectedValues) {
                        if ($this->isUserNameRelationColumn($relation, $relatedColumn)) {
                            $relationQuery->whereIn(DB::raw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))"), $selectedValues);
                            return;
                        }

                        $relationQuery->whereIn($relatedColumn, $selectedValues);
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
                [$relation, $relatedColumn] = explode('.', $column, 2);
                $query->whereHas($relation, function ($relationQuery) use ($relation, $relatedColumn, $keyword) {
                    $this->applyRelationKeywordCondition($relationQuery, $relation, $relatedColumn, $keyword);
                });
            } else {
                $query->where($column, 'like', '%' . $keyword . '%');
            }
        }

        return $query;
    }

    protected function buildFilteredQuery()
    {
        $query = $this->buildBaseQuery();
        $query = $this->applyColumnFilters($query);

        $keyword = trim($this->search);
        $searchColumns = $this->getSearchColumns();

        if ($keyword !== '') {
            $query->where(function ($nestedQuery) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relatedColumn] = explode('.', $column, 2);
                        $nestedQuery->orWhereHas($relation, function ($relationQuery) use ($relation, $relatedColumn, $keyword) {
                            $this->applyRelationKeywordCondition($relationQuery, $relation, $relatedColumn, $keyword);
                        });
                    } else {
                        $nestedQuery->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            });
        }

        return $query;
    }

    protected function isUserNameRelationColumn(string $relation, string $relatedColumn): bool
    {
        return in_array($relation, ['user', 'createdBy', 'resolvedBy'], true)
            && in_array($relatedColumn, ['name', 'full_name'], true);
    }

    protected function applyRelationKeywordCondition($relationQuery, string $relation, string $relatedColumn, string $keyword): void
    {
        if ($this->isUserNameRelationColumn($relation, $relatedColumn)) {
            $relationQuery->where(function ($nameQuery) use ($keyword) {
                $nameQuery->where('first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) like ?", ['%' . $keyword . '%']);
            });

            return;
        }

        $relationQuery->where($relatedColumn, 'like', '%' . $keyword . '%');
    }

    public function exportCurrentList()
    {
        $rows = $this->buildFilteredQuery()->get();
        $headers = $this->headers;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnIndex = 1;
        foreach ($headers as $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', $label);
            $columnIndex++;
        }

        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $columnIndex = 1;
            foreach (array_keys($headers) as $key) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex) . $rowNumber,
                    $this->formatExportValue($row, $key)
                );
                $columnIndex++;
            }
            $rowNumber++;
        }

        foreach (range(1, count($headers)) as $columnNumber) {
            $sheet->getColumnDimensionByColumn($columnNumber)->setAutoSize(true);
        }

        $fileName = Str::slug($this->title ?: $this->type) . '-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function formatExportValue($row, string $key): string
    {
        $value = data_get($row, $key);

        if ($value instanceof Collection) {
            return $value->pluck('name')->filter()->implode(', ');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item) => (string) $item, $value));
        }

        if (is_object($value)) {
            foreach (['name', 'title', 'email', 'full_name', 'id'] as $property) {
                $objectValue = data_get($value, $property);
                if ($objectValue !== null && $objectValue !== '') {
                    return (string) $objectValue;
                }
            }

            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return '-';
        }

        return (string) $value;
    }

    protected function applyTypeScopes($query)
    {
        return match ($this->type) {
            'fixture-audit-logs' => $query->where(function ($q) {
                $q->whereNotNull('fixture_id')
                    ->orWhere(function ($q2) {
                        $q2->whereNull('tester_id')->whereNull('spare_part_id')->where('explanation', 'like', '%fixture%');
                    });
            }),
            'tester-audit-logs' => $query->where(function ($q) {
                $q->where(function ($subQ) {
                    $subQ->whereNotNull('tester_id')
                        ->whereNull('fixture_id')
                        ->whereNull('spare_part_id');
                })->orWhere('explanation', 'like', 'Deleted tester details%');
            }),
            'inventory-audit-logs' => $query->where(function($q) {
                $q->whereNotNull('spare_part_id')
                  ->orWhereNotNull('spare_part_supplier_id')
                  ->orWhere('explanation', 'like', '%spare part%')
                  ->orWhere('explanation', 'like', '%supplier%');
            }),
            'issues' => $query->activeIssueRows()->orderByDesc('date'),
            'issue-history' => $query
                ->where('description', 'not like', '[HISTORY]%')
                ->where(function ($historyQuery) {
                    $historyQuery->where(function ($problemQuery) {
                        $problemQuery->problems()->whereNull('parent_event_log_id');
                    })->orWhere(function ($solutionQuery) {
                        $solutionQuery->solutions();
                    });
                })
                ->orderByDesc('date'),
            default => $query,
        };
    }

    public function render()
    {
        $this->filters = $this->getFiltersConfig();
        $this->normalizeColumnFilters();
        $query = $this->buildFilteredQuery();

        if ($this->goToLastPageOnRender) {
            $totalRows = (clone $query)->count();
            $lastPage = max((int) ceil($totalRows / 10), 1);
            $this->setPage($lastPage);
            $this->goToLastPageOnRender = false;
        }

        return view('livewire.components.data-table', [
            'data' => $query->paginate(10),
        ]);
    }
}
