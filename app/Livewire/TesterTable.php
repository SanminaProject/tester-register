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
        'Operatying System',
        'ID by Customer',
        'Status'
    ];

    public function render()
    {
        $testers = Tester::all();
        return view('livewire.tester-table', [
            'testers' => $testers,
            'headers' => $this->headers,
        ]);
    }
}
