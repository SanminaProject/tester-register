<div class="flex w-full">
    @php
    $issueItems = [
        ['label' => 'Active Issues', 'tab' => 'all'],
        ['label' => 'Issue History', 'tab' => 'logs'],
    ];
    
    if (auth()->user() && !auth()->user()->hasRole('Guest')) {
        array_splice($issueItems, 1, 0, [['label' => 'Add New Issue', 'tab' => 'add']]);
    }
    @endphp
    
    <x-sidebar
        title="Issues"
        :active-tab="$activeTab"
        :items="$issueItems" />

    <div class="flex-1 min-w-0 px-6 py-3">
        @if ($activeTab === 'logs')
        <livewire:pages.issues.issue-history />
        @else
        <livewire:pages.issues.issue-workbench
            :requested-tab="$activeTab"
            :requested-issue-id="$selectedIssueId"
            wire:key="issue-workbench-{{ $activeTab }}-{{ $selectedIssueId ?? 'none' }}" />
        @endif
    </div>
</div>