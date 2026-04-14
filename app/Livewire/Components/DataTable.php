<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tester;
use App\Models\Fixture;
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
            default => throw new \Exception("Invalid data type"),
        };
    }

    protected function getRelations()
    {
        return match ($this->type) {
            'testers' => ['owner', 'statusRelation'],
            'fixtures' => [],
            default => [],
        };
    }

    protected function getSearchColumns()
    {
        return match ($this->type) {
            'testers' => ['name', 'description', 'operating_system'],
            'fixtures' => ['name', 'description', 'manufacturer'],
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

    public function render()
    {
        $model = $this->getModelClass();
        $this->filters = $this->getFiltersConfig();
        $query = $model::with($this->getRelations());
        $keyword = trim($this->search);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                foreach ($this->getSearchColumns() as $column) {
                    $q->orWhere($column, 'like', '%' . $keyword . '%');
                }
            });
        }

        $data = $query->paginate(10);

        return view('livewire.components.data-table', [
            'data' => $data
        ]);
    }
}
