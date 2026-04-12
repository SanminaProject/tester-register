<div class="flex">
<!-- To be fixed later: Not sure if we should use tabs or links for the sidebar.  -->
    <x-sidebar 
        title="Fixtures" 
        :active-tab="$activeTab"
        :items="[
            ['label' => 'All Fixtures', 'tab' => 'all'],
            ['label' => 'Add New Fixture', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
            ['label' => 'Add New Log', 'tab' => 'addlog']
        ]" 
    />
        
    <div class="flex-1 p-8">
        @if ($activeTab === 'all')
        <livewire:pages.fixtures.fixture-table />
        @elseif ($activeTab === 'add')
        <livewire:pages.fixtures.fixture-logging />
        @elseif ($activeTab === 'logs')
        <livewire:audit-logs />
        @elseif ($activeTab === 'addlog')
        <livewire:add-new-log />
        @endif
    </div>
</div>
