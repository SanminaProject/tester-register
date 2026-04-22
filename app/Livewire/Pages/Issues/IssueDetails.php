<?php

namespace App\Livewire\Pages\Issues;

use App\Models\EventType;
use App\Models\TesterEventLog;
use Illuminate\Support\Facades\Auth;
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
        $actorId = Auth::id() ?? 1;
        $eventTypeId = EventType::query()
            ->whereRaw('LOWER(name) = ?', ['issue'])
            ->value('id');

        if ($eventTypeId) {
            TesterEventLog::create([
                'date' => now(),
                'description' => '[HISTORY] Deleted issue #' . $this->issue->id,
                'tester_id' => $this->issue->tester_id,
                'event_type' => (int) $eventTypeId,
                'created_by_user_id' => $actorId,
                'issue_status' => null,
                'resolution_description' => null,
                'resolved_date' => null,
                'resolved_by_user_id' => null,
            ]);
        }

        $this->issue->delete();

        session()->flash('message', 'Issue deleted successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-details');
    }
}
