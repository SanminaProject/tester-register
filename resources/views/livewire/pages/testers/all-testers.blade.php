<div>
   <livewire:components.data-table
        type="testers"
        title="Tester List"
        searchPlaceholder="Search testers..."
        addButtonLabel="Add Tester"
        :headers="[
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'type' => 'Type',
            'operating_system' => 'Operating System',
            'id_number_by_customer' => 'ID by Customer',
            'status' => 'Status'
        ]"
    />
</div>