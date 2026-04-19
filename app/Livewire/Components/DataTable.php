<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tester;
use App\Models\Fixture;
use App\Models\DataChangeLog;
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
        return view()->exists("livewire.pages.{$plural}.{$singular}-details");
    }

    protected function getModelClass()
    {
        return match ($this->type) {
            'testers' => Tester::class,
            'fixtures' => Fixture::class,
            'fixture-audit-logs' => DataChangeLog::class,
            'tester-audit-logs' => DataChangeLog::class,
            default => throw new \Exception("Invalid data type"),
        };
    }

    protected function getRelations()
    {
        return match ($this->type) {
            'testers' => ['owner', 'statusRelation'],
            'fixtures' => ['tester', 'location', 'status'],
            'fixture-audit-logs' => ['fixture', 'user'],
            'tester-audit-logs' => ['tester', 'user'],
            default => [],
        };
    }

    protected function getSearchColumns()
    {
        return match ($this->type) {
            'testers' => ['name', 'description', 'operating_system'],
            'fixtures' => ['name', 'description', 'manufacturer'],
            'fixture-audit-logs' => ['explanation', 'fixture.name', 'user.email'],
            'tester-audit-logs' => ['explanation', 'tester.name', 'user.email'],
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
            'fixture-audit-logs' => $query->where(function($q) {
                $q->whereNotNull('fixture_id')
                  ->orWhere(function($q2) {
                      $q2->whereNull('tester_id')->whereNull('spare_part_id')->where('explanation', 'like', '%fixture%');
                  });
            }),
            'tester-audit-logs' => $query->where(function($q) {
                $q->whereNotNull('tester_id')
                  ->whereNull('fixture_id')
                  ->whereNull('spare_part_id');
            }),
            'spare-part-audit-logs' => $query->where(function($q) {
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

        $query = $model::with($this->getRelations());
        $query = $this->applyTypeScopes($query);

        $keyword = trim($this->search);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                foreach ($this->getSearchColumns() as $column) {
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
