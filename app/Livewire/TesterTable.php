<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Tester;

class TesterTable extends Component
{
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

    public function render()
    {
        $query = Tester::query();
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $testers = $query->paginate(10);

        return view('livewire.tester-table', [
            'testers' => $testers,
            'headers' => $this->headers,
        ]);
    }
}
