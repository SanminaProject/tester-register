<div>
   <livewire:components.data-table
        type="suppliers"
        title="Supplier List"
        searchPlaceholder="Search suppliers..."
        addButtonLabel="Add Supplier"
        :headers="[
            'id' => 'ID',
            'supplier_name' => 'Name',
            'contact_person' => 'Contact Person',
            'contact_email' => 'Contact Email',
            'contact_phone' => 'Contact Phone',
            'address' => 'Address',
            'spare_parts_count' => 'Number of Testers',
            'created_at' => 'Created at'
        ]"
    />
</div>