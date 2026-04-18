<?php

namespace App\Livewire\Pages\Issues;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class IssuePage extends Component
{
    #[Url]
    public string $activeTab = 'all';

    #[Url(except: null)]
    public ?int $selectedIssueId = null;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'all', $id = null): void
    {
        if (is_array($tab)) {
            $this->activeTab = $tab['tab'] ?? 'all';
            $this->selectedIssueId = isset($tab['id']) ? (int) $tab['id'] : null;

            return;
        }

        $this->activeTab = $tab ?: 'all';
        $this->selectedIssueId = $id ? (int) $id : null;
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-page');
    }
}
