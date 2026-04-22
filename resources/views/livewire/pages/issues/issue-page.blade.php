<div class="flex w-full">
    <x-sidebar
        title="Issues"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Active Issues', 'tab' => 'all'],
            ['label' => 'Add New Issue', 'tab' => 'add'],
            ['label' => 'Issue History', 'tab' => 'logs'],
        ]" />

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