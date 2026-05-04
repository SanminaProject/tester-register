<div>
   <livewire:components.data-table
        type="spare-parts"
        title="Spare Part List"
        searchPlaceholder="Search parts..."
        addButtonLabel="Add Part"
        :headers="[
            'id' => 'ID',
            'name' => 'Name',
            'tester_id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'quantity_in_stock' => 'In Stock',
            'reorder_level' => 'Reorder Level',
            'last_order_date' => 'Last Ordered',
            'needs_reorder' => 'Stock Status',
            'unit_price' => 'Unit Price',
            'description' => 'Description',
            'manufacturer_part_number' => 'Manufacturer Part Number',
            'supplier_id' => 'Supplier ID',
            'supplier.supplier_name' => 'Supplier Name',
            'responsible_user_names' => 'Responsible Users',
            'tester_responsible_user_names' => 'Responsible Users for Associated Tester'
        ]"
    />
</div>