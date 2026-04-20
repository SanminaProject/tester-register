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
        $this->activeTab = $this->normalizeTab($tab);
    }

    public function updatedActiveTab(string $value): void
    {
        if ($value !== 'solution') {
            $this->selectedIssueId = null;
        }

        if ($value === 'logs') {
            return;
        }

        $this->dispatch('issue-mode-requested', tab: $value, id: $this->selectedIssueId);
    }

    #[On('switchTab')]
    public function switchTab($tab = 'all', $id = null): void
    {
        if (is_array($tab)) {
            $this->activeTab = $this->normalizeTab((string) ($tab['tab'] ?? 'all'));
            $this->selectedIssueId = isset($tab['id']) ? (int) $tab['id'] : null;

            return;
        }

        $this->activeTab = $this->normalizeTab((string) ($tab ?: 'all'));
        $this->selectedIssueId = $id ? (int) $id : null;
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, ['all', 'add', 'solution', 'logs'], true) ? $tab : 'all';
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-page');
    }
}
