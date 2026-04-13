<?php

namespace App\Livewire\Pages\Fixtures;

use App\Livewire\Forms\FixtureForm;
use Livewire\Component;

class FixtureLogging extends Component
{
    public FixtureForm $form;

    public function save()
    {
        $this->form->save();

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'all');
    }
}