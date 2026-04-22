<div class="flex w-full">
    <x-sidebar 
        title="Inventory" 
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Spare Parts', 'tab' => 'spare-parts'], // list all spare parts
            ['label' => 'Suppliers', 'tab' => 'suppliers'], // list all suppliers
            ['label' => 'Audit Logs', 'tab' => 'logs']
        ]" 
    />
        
    <div class="flex-1 min-w-0 px-6 py-3">
        @if ($activeTab === 'spare-parts')
        <livewire:pages.inventory.spare-parts.spare-parts-table />
        @elseif ($activeTab === 'suppliers')
        <livewire:pages.inventory.suppliers.suppliers-table />
        @elseif ($activeTab === 'logs')
        <livewire:pages.inventory.inventory-audit-logs />
        @elseif ($activeTab === 'spare-part-details')
        <livewire:pages.inventory.spare-parts.spare-part-details :sparePartId="$selectedSparePartId" wire:key="spare-part-details-{{ $selectedSparePartId }}" />
        @elseif ($activeTab === 'edit')
        <livewire:pages.fixtures.fixture-logging :fixtureId="$selectedFixtureId" wire:key="fixture-edit-{{ $selectedFixtureId }}" />
        @endif
    </div>
</div>
