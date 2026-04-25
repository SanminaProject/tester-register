<div>
   <livewire:components.data-table
        type="spare-parts"
        title="Spare Part List"
        searchPlaceholder="Search parts..."
        addButtonLabel="Add Part"
        :headers="[
            'id' => 'ID',
            'needs_reorder' => 'Stock Status',
            'name' => 'Name',
            'description' => 'Description',
            'manufacturer_part_number' => 'Manufacturer Part Number',
            'quantity_in_stock' => 'In Stock',
            'unit_price' => 'Unit Price',
            'last_order_date' => 'Last Ordered',
            'tester_id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'supplier_id' => 'Supplier ID',
            'supplier.supplier_name' => 'Supplier Name',
            'responsible_user_names' => 'Responsible Users',
            'tester_responsible_user_names' => 'Tester Responsible Users',
            'reorder_level' => 'Reorder Level'
        ]"
    />
</div>