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
            'location_id' => 'Location',
            'fixture_status' => 'Status'
        ]"
    />
</div>