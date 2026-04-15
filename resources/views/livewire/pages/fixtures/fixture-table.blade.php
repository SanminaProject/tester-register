<div>
   <livewire:components.data-table
        type="fixtures"
        title="Fixture List"
        searchPlaceholder="Search fixtures..."
        addButtonLabel="Add Fixture"
        :headers="[
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'manufacturer' => 'Manufacturer',
            'tester_id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'location.name' => 'Location',
            'status.name' => 'Status'
        ]"
    />
</div>