<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\User;

class MultipleChoiceSelector extends Component
{
    public array $selectedIds = [];

    public $users = [];
    public string $placeholder = 'Select recipient';

    public function mount($selectedIds = [], $users = null, $placeholder = null)
    {
        $this->users = $users ?? User::all();
        $this->selectedIds = $selectedIds;
        $this->placeholder = $placeholder ?? $this->placeholder;
    }

    public function addRecipient($userId)
    {
        if (!$userId) return;

        if (!in_array($userId, $this->selectedIds)) {
            $this->selectedIds[] = (int) $userId;
        }

        $this->dispatch('recipientsUpdated', selectedIds: $this->selectedIds);
    }

    public function removeRecipient($userId)
    {
        $this->selectedIds = array_values(
            array_filter($this->selectedIds, fn ($id) => $id != $userId)
        );

        $this->dispatch('recipientsUpdated', selectedIds: $this->selectedIds);
    }

    public function getSelectedRecipientsProperty()
    {
        return User::whereIn('id', $this->selectedIds)->get();
    }

    public function render()
    {
        return view('livewire.components.multiple-choice-selector');
    }
}