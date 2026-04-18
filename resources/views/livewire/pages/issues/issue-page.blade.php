<div class="flex w-full">
    <x-sidebar
        class="{{ $activeTab === 'details' || $activeTab === 'edit' ? 'hidden md:block' : '' }}"
        title="Issues"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Active Issues', 'tab' => 'all'],
            ['label' => 'Add New Issue', 'tab' => 'add'],
            ['label' => 'Issue History', 'tab' => 'logs'],
        ]" />

    <div class="flex-1 min-w-0 px-6 py-3">
        @if ($activeTab === 'all')
            <livewire:pages.issues.active-issues />
        @elseif ($activeTab === 'add')
            <livewire:pages.issues.add-new-issue />
        @elseif ($activeTab === 'logs')
            <livewire:pages.issues.issue-history />
        @elseif ($activeTab === 'details')
            <livewire:pages.issues.issue-details :issueId="$selectedIssueId" wire:key="issue-details-{{ $selectedIssueId }}" />
        @elseif ($activeTab === 'edit')
            <livewire:pages.issues.add-new-issue :issueId="$selectedIssueId" wire:key="issue-edit-{{ $selectedIssueId }}" />
        @endif
    </div>
</div>
