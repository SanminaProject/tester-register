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

    public function render()
    {
        return view('livewire.pages.testers.tester-details');
    }
}
