<?php

namespace App\Livewire\Pages\Inventory\SpareParts;

use Livewire\Component;
use App\Models\User;
use App\Models\TesterSparePart;
use App\Livewire\Forms\EmailMessageForm;

class EmailForm extends Component
{
    public EmailMessageForm $form;

    public $users = [];
    public TesterSparePart $sparePart;

    public function mount($sparePartId)
    {
        $this->sparePart = TesterSparePart::with('responsibleUsers')->findOrFail($sparePartId);
        $this->users = User::all();

        $this->form->subject = "Spare Part Needs Reordering: {$this->sparePart->name}";

        $this->form->responsible_user_ids = $this->sparePart
        ->responsibleUsers
        ->pluck('id')
        ->toArray();
    }

    #[\Livewire\Attributes\On('recipientsUpdated')]
    public function updateRecipients($selectedIds)
    {
        $this->form->responsible_user_ids = $selectedIds;
    }

    public function save()
    {
        $this->form->send($this->sparePart);
        $this->dispatch('switchTab', tab: 'spare-parts');
        $this->dispatch('switchTab', tab: 'spare-part-details', sparePartId: $this->sparePart->id);
    }

    public function render()
    {
        return view('livewire.pages.inventory.spare-parts.email-form');
    }
}