<div class="flex">
<!-- To be fixed later: Not sure if we should use tabs or links for the sidebar.  -->
    <x-sidebar 
        title="Fixtures" 
        :active-tab="$activeTab"
        :items="[
            ['label' => 'All Fixtures', 'tab' => 'all'],
            ['label' => 'Add New Fixture', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs']
        ]" 
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
        @endif
    </div>
</div>
