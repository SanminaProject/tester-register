<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tester;
use App\Models\Fixture;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\DataChangeLog;
use App\Models\TesterEventLog;
use Illuminate\Support\Str;

class DataTable extends Component
{
    use WithPagination;

    public $headers = [];
    public $type = 'testers'; // or fixtures etc.

    public $search = '';

    public $title = 'Data List';
    public $searchPlaceholder = 'Search...';
    public $addButtonLabel = 'Add';
    public $showAddButton = true;

    public $filters = [];
    public $activeFilters = [];

    public function getHasDetailsProperty()
    {
        $plural = Str::plural($this->type);
        $singular = Str::singular($this->type);

        if (view()->exists("livewire.pages.admin.{$plural}.{$singular}-details") || view()->exists("livewire.pages.admin.{$singular}.{$singular}-details")) {
            return true;
        }

        return view()->exists("livewire.pages.{$plural}.{$singular}-details");
    }

    protected function getModelClass()
    {
        return match ($this->type) {
            'testers' => Tester::class,
            'fixtures' => Fixture::class,
            'personnel' => User::class,
            'roles' => Role::class,
            'fixture-audit-logs' => DataChangeLog::class,
            'tester-audit-logs' => DataChangeLog::class,
            'issues' => TesterEventLog::class,
            'issue-history' => TesterEventLog::class,
            default => throw new \Exception("Invalid data type"),
        };
    }

    protected function getRelations()
    {
        return match ($this->type) {
            'testers' => ['owner', 'statusRelation'],
            'fixtures' => ['tester', 'location', 'status'],
            'personnel' => ['roles', 'testers'],
            'fixture-audit-logs' => ['fixture', 'user'],
            'tester-audit-logs' => ['tester', 'user'],
            'issues' => ['tester', 'createdBy', 'issueStatusRelation', 'eventType'],
            'issue-history' => ['tester', 'createdBy', 'issueStatusRelation', 'eventType'],
            default => [],
        };
    }

    protected function getSearchColumns()
    {
        return match ($this->type) {
            'testers' => ['name', 'description', 'operating_system'],
            'fixtures' => ['name', 'description', 'manufacturer'],
            'personnel' => ['first_name', 'last_name', 'email'],
            'roles' => ['name', 'guard_name'],
            'fixture-audit-logs' => ['explanation', 'fixture_id', 'fixture.name', 'user.email'],
            'tester-audit-logs' => ['explanation', 'tester_id', 'tester.name', 'user.email'],
            'fixture-audit-logs' => ['explanation', 'fixture.name', 'user.email'],
            'tester-audit-logs' => ['explanation', 'tester.name', 'user.email'],
            'issues' => ['id', 'date', 'tester_id', 'eventType.name', 'description', 'createdBy.email', 'issueStatusRelation.name'],
            'issue-history' => ['id', 'date', 'tester_id', 'eventType.name', 'description', 'createdBy.email', 'issueStatusRelation.name'],
            default => [],
        };
    }

    protected function getFiltersConfig()
    {
        return match ($this->type) {
            'testers' => [
                'id' => 'ID',
                'name' => 'Name',
                'description' => 'Description',
                'operating_system' => 'OS',
                'owner_id' => 'Owner',
                'status_id' => 'Status',
            ],
            'fixtures' => [
                'id' => 'ID',
                'name' => 'Name',
                'description' => 'Description',
                'manufacturer' => 'Manufacturer',
            ],
            'issues' => [
                'id' => 'Log ID',
                'date' => 'Date',
                'tester_id' => 'Test ID',
                'eventType.name' => 'Type',
                'description' => 'Description',
                'createdBy.email' => 'User',
                'issueStatusRelation.name' => 'Status',
            ],
            'issue-history' => [
                'id' => 'Log ID',
                'date' => 'Date',
                'tester_id' => 'Test ID',
                'eventType.name' => 'Type',
                'description' => 'Description',
                'createdBy.email' => 'User',
                'issueStatusRelation.name' => 'Status',
            ],
            default => [],
        };
    }

    public function toggleFilter($filter)
    {
        if (in_array($filter, $this->activeFilters)) {
            $this->activeFilters = array_values(array_diff($this->activeFilters, [$filter]));
        } else {
            $this->activeFilters[] = $filter;
        }

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function applyTypeScopes($query)
    {
        return match ($this->type) {
            'fixture-audit-logs' => $query->where(function ($q) {
                $q->whereNotNull('fixture_id')
                    ->orWhere('explanation', 'like', '%fixture%');
            }),
            'tester-audit-logs' => $query->where(function ($q) {
                $q->whereNotNull('tester_id')
                    ->orWhere('explanation', 'like', '%tester%');
            }),
            'issues' => $query
                ->activeIssueRows()
                ->orderByDesc('date'),
            'issue-history' => $query
                ->where('description', 'not like', '[HISTORY]%')
                ->where(function ($historyQuery) {
                    $historyQuery->where(function ($problemQuery) {
                        $problemQuery->problems()
                            ->whereNull('parent_event_log_id');
                    })->orWhere(function ($solutionQuery) {
                        $solutionQuery->solutions();
                    });
                })
                ->orderByDesc('date'),
            'spare-part-audit-logs' => $query->where(function ($q) {
                $q->whereNotNull('spare_part_id')
                    ->orWhere('explanation', 'like', '%spare part%');
            }),
            default => $query,
        };
    }

    public function render()
    {
        $model = $this->getModelClass();
        $this->filters = $this->getFiltersConfig();

        // base query
        $query = $model::query();

        // relations if needed
        $relations = $this->getRelations();
        if (!empty($relations)) {
            $query->with($relations);
        }

        // count users for roles table
        if ($this->type === 'roles') {
            $query->withCount('users');
        }

        // type-specific scopes
        $query = $this->applyTypeScopes($query);

        if (in_array($this->type, ['fixture-audit-logs', 'tester-audit-logs', 'spare-part-audit-logs'])) {
            $query->orderByDesc('changed_at')->orderByDesc('id');
        }

        $keyword = trim($this->search);
        $searchColumns = $this->getSearchColumns();

        if (! empty($this->activeFilters)) {
            $filteredColumns = array_values(array_intersect($searchColumns, $this->activeFilters));
            if (! empty($filteredColumns)) {
                $searchColumns = $filteredColumns;
            }
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        $q->orWhereHas($relation, function ($relQuery) use ($relColumn, $keyword) {
                            $relQuery->where($relColumn, 'like', '%' . $keyword . '%');
                        });
                    } else {
                        $q->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            });
        }

        $data = $query->paginate(10);

        return view('livewire.components.data-table', [
            'data' => $data
        ]);
    }
}
