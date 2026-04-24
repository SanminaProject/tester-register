<div>
   <livewire:components.data-table
        type="spare-parts"
        title="Spare Part List"
        searchPlaceholder="Search parts..."
        addButtonLabel="Add Part"
        :headers="[
            'id' => 'ID',
            'needs_reorder' => 'Needs Reordering',
            'name' => 'Name',
            'description' => 'Description',
            'manufacturer_part_number' => 'Manufacturer Part Number',
            'quantity_in_stock' => 'Quantity in Stock',
            'unit_price' => 'Unit Price',
            'last_order_date' => 'Last Order Date',
            'tester_id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'supplier_id' => 'Supplier ID',
            'supplier.supplier_name' => 'Supplier Name',
            'reorder_level' => 'Reorder Level'
        ]"
    />
</div>