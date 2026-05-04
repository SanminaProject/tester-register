<div class="flex w-full">
    @php
    $inventoryItems = [
        ['label' => 'Spare Parts', 'tab' => 'spare-parts'],
        ['label' => 'Suppliers', 'tab' => 'suppliers'],
        ['label' => 'Audit Logs', 'tab' => 'logs']
    ];
    
    if (auth()->user() && !auth()->user()->hasRole('Guest')) {
        array_splice($inventoryItems, 2, 0, [
            ['label' => 'Add Spare Part', 'tab' => 'add-spare-part'],
            ['label' => 'Add Supplier', 'tab' => 'add-supplier'],
        ]);
    }
    @endphp
    
    <x-sidebar 
        title="Inventory" 
        :active-tab="$activeTab"
        :items="$inventoryItems" 
    />
        
    <div class="flex-1 min-w-0 px-6 py-3">
        @if ($activeTab === 'spare-parts')
        <livewire:pages.inventory.spare-parts.spare-parts-table />
        @elseif ($activeTab === 'suppliers')
        <livewire:pages.inventory.suppliers.suppliers-table />
        @elseif ($activeTab === 'add-spare-part')
        <livewire:pages.inventory.spare-parts.spare-part-logging />
        @elseif ($activeTab === 'add-supplier')
        <livewire:pages.inventory.suppliers.supplier-logging />
        @elseif ($activeTab === 'logs')
        <livewire:pages.inventory.inventory-audit-logs />
        @elseif ($activeTab === 'email-form')
        <livewire:pages.inventory.spare-parts.email-form :sparePartId="$selectedSparePartId" wire:key="email-form-{{ $selectedSparePartId }}" />
        @elseif ($activeTab === 'spare-part-details')
        <livewire:pages.inventory.spare-parts.spare-part-details :sparePartId="$selectedSparePartId" wire:key="spare-part-details-{{ $selectedSparePartId }}" />
        @elseif ($activeTab === 'supplier-details')
        <livewire:pages.inventory.suppliers.supplier-details :sparePartSupplierId="$selectedSparePartSupplierId" wire:key="supplier-details-{{ $selectedSparePartSupplierId }}" />
        @elseif ($activeTab === 'edit-spare-parts')
        <livewire:pages.inventory.spare-parts.spare-part-logging :sparePartId="$selectedSparePartId" wire:key="spare-part-edit-{{ $selectedSparePartId }}" />
        @elseif ($activeTab === 'edit-suppliers')
        <livewire:pages.inventory.suppliers.supplier-logging :sparePartSupplierId="$selectedSparePartSupplierId" wire:key="supplier-edit-{{ $selectedSparePartSupplierId }}" />
        @endif
    </div>
</div>
