<div>
    <livewire:components.data-table
        type="testers"
        title="Testers List"
        searchPlaceholder="Search testers..."
        addButtonLabel="Add Tester"
        :headers="[
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'product_family' => 'Prod Family',
            'owner.name' => 'Owner', 
            'id_number_by_customer' => 'Customer ID',
            'statusRelation.name' => 'Status', 
            'location.name' => 'Location',
            'type' => 'Type',
            'manufacturer' => 'Manufacturer',
            'operating_system' => 'OS',
            'implementation_date' => 'Implementation Date',
        ]" />
</div>