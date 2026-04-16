<div class="flex w-full">
    <x-sidebar
        title="Testers"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'All Testers', 'tab' => 'all'],
            ['label' => 'Add New Tester', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
        ]" />

    <div class="flex-1  min-w-0 px-6 py-3">
        @if ($activeTab === 'all')
        <livewire:pages.testers.all-testers />
        @elseif ($activeTab === 'add')
        <livewire:pages.testers.add-new-tester />
        @elseif ($activeTab === 'logs')
        <livewire:pages.testers.tester-audit-logs />
        @elseif ($activeTab === 'details')
        <livewire:pages.testers.tester-details :testerId="$selectedTesterId" wire:key="tester-details-{{ $selectedTesterId }}" />
        @endif
    </div>
</div>