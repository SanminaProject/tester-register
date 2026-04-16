<?php

namespace App\Livewire\Pages\Testers;

use App\Models\Tester;
use Livewire\Component;

class TesterDetails extends Component
{
    public Tester $tester;

    public function mount($testerId)
    {
        $this->tester = Tester::with(['owner', 'statusRelation', 'location'])->findOrFail($testerId);
    }

    public function updateInventoryDate()
    {
        $this->tester->update([
            'last_inventoried_date' => now()
        ]);
        $this->tester->refresh();
    }

    public function render()
    {
        return view('livewire.pages.testers.tester-details');
    }
}
