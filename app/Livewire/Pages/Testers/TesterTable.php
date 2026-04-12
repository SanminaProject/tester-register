<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Tester;

class TesterTable extends Component
{
    use WithPagination;

    public $search = '';

    public $headers = [
        'ID',
        'Name',
        'Description',
        'Type',
        'Operating System',
        'ID by Customer',
        'Status'
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tester::query();

        $keyword = trim($this->search);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('operating_system', 'like', '%' . $keyword . '%');
            });
        }

        $testers = $query->paginate(10);

        return view('livewire.pages.testers.tester-table', [
            'testers' => $testers,
            'headers' => $this->headers,
        ]);
    }
}
