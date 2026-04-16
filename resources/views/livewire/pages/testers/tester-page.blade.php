<div class="flex w-full">
    <x-sidebar
        class="{{ $activeTab === 'details' ? 'hidden md:block' : '' }}"
        title="Testers"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'All Testers', 'tab' => 'all'],
            ['label' => 'Add New Tester', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
            ['label' => 'Add New Log', 'tab' => 'addlog']
        ]" />

    <div class="flex-1  min-w-0 px-6 py-3">
        @if ($activeTab === 'all')
        <livewire:pages.testers.all-testers />
        @elseif ($activeTab === 'add')
        <livewire:pages.testers.add-new-tester />
        @elseif ($activeTab === 'logs')
        <livewire:audit-logs />
        @elseif ($activeTab === 'addlog')
        <livewire:add-new-log />
        @elseif ($activeTab === 'details')
        <livewire:pages.testers.tester-details :testerId="$selectedTesterId" wire:key="tester-details-{{ $selectedTesterId }}" />
        @endif
    </div>
</div>