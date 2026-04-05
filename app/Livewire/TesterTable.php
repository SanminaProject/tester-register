<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Tester;

class TesterTable extends Component
{
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
        $testers = Tester::paginate(10); // Fetch testers with pagination
        return view('livewire.tester-table', [
            'testers' => $testers,
            'headers' => $this->headers,
        ]);
    }
}
