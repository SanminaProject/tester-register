<div class="flex w-full">
    @php
    $testerItems = [
        ['label' => 'All Testers', 'tab' => 'all'],
        ['label' => 'Audit Logs', 'tab' => 'logs'],
    ];
    
    if (auth()->user() && !auth()->user()->hasRole('Guest')) {
        array_splice($testerItems, 1, 0, [['label' => 'Add New Tester', 'tab' => 'add']]);
    }
    @endphp
    
    <x-sidebar
        class="{{ $activeTab === 'details' ? 'hidden md:block' : '' }}"
        title="Testers"
        :active-tab="$activeTab"
        :items="$testerItems" />

    <div class="flex-1  min-w-0 px-6 py-3">
        @if ($activeTab === 'all')
        <livewire:pages.testers.all-testers />
        @elseif ($activeTab === 'add')
        <livewire:pages.testers.add-new-tester key="add-tester" />
        @elseif ($activeTab === 'edit')
        <livewire:pages.testers.add-new-tester :testerId="$selectedTesterId" wire:key="edit-tester-{{ $selectedTesterId }}" />
        @elseif ($activeTab === 'logs')
        <livewire:pages.testers.tester-audit-logs />
        @elseif ($activeTab === 'details')
        <livewire:pages.testers.tester-details :testerId="$selectedTesterId" wire:key="tester-details-{{ $selectedTesterId }}" />
        @endif
    </div>
</div>