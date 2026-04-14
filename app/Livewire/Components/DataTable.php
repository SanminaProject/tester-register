<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tester;
use App\Models\Fixture;

class DataTable extends Component
{
    use WithPagination;

    public $headers = [];
    public $type = 'testers'; // or fixtures etc.

    public $search = '';

    public $title = 'Data List';
    public $searchPlaceholder = 'Search...';
    public $addButtonLabel = 'Add';

    public function getHasDetailsProperty()
    {
        $plural = \Illuminate\Support\Str::plural($this->type);
        $singular = \Illuminate\Support\Str::singular($this->type);
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

    protected function getSearchColumns()
    {
        return match ($this->type) {
            'testers' => ['name', 'description', 'operating_system'],
            'fixtures' => ['name', 'description', 'manufacturer'],
            default => [],
        };
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $model = $this->getModelClass();


        $query = $model::with([
            'owner',
            'statusRelation'
        ]);

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
