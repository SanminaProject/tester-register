<?php

namespace App\Livewire\Pages\Issues;

use App\Models\TesterEventLog;
use Livewire\Component;

class IssueDetails extends Component
{
    public TesterEventLog $issue;

    public function mount($issueId): void
    {
        $this->issue = TesterEventLog::query()
            ->with(['tester', 'createdBy', 'issueStatusRelation'])
            ->activeIssueRows()
            ->findOrFail((int) $issueId);
    }

    public function editIssue(): void
    {
        $this->dispatch('switchTab', tab: 'edit', id: $this->issue->id);
    }

    public function deleteIssue(): void
    {
        $this->issue->delete();

        session()->flash('message', 'Issue deleted successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-details');
    }
}
