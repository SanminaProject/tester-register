<div class="flex">
<!-- To be fixed later: Not sure if we should use tabs or links for the sidebar.  -->
    @php
    $fixtureItems = [
        ['label' => 'All Fixtures', 'tab' => 'all'],
        ['label' => 'Audit Logs', 'tab' => 'logs']
    ];
    
    if (auth()->user() && !auth()->user()->hasRole('Guest')) {
        array_splice($fixtureItems, 1, 0, [['label' => 'Add New Fixture', 'tab' => 'add']]);
    }
    @endphp
    
    <x-sidebar 
        title="Fixtures" 
        :active-tab="$activeTab"
        :items="$fixtureItems" 
    />
        
    <div class="flex-1 px-6 py-3">
        @if ($activeTab === 'all')
        <livewire:pages.fixtures.fixture-table />
        @elseif ($activeTab === 'add')
        <livewire:pages.fixtures.fixture-logging />
        @elseif ($activeTab === 'logs')
        <livewire:pages.fixtures.fixture-audit-logs />
        @elseif ($activeTab === 'details')
        <livewire:pages.fixtures.fixture-details :fixtureId="$selectedFixtureId" wire:key="fixture-details-{{ $selectedFixtureId }}" />
        @elseif ($activeTab === 'edit')
        <livewire:pages.fixtures.fixture-logging :fixtureId="$selectedFixtureId" wire:key="fixture-edit-{{ $selectedFixtureId }}" />
        @endif
    </div>
</div>
