<div>
    <livewire:components.data-table
        type="issues"
        title="Active Issues"
        searchPlaceholder="Search issues..."
        addButtonLabel="Add Issue"
        :headers="[
            'id' => 'Log ID',
            'date' => 'Date',
            'tester_id' => 'Test ID',
            'eventType.name' => 'Type',
            'description' => 'Description',
            'createdBy.email' => 'User',
            'issueStatusRelation.name' => 'Status',
        ]" />
</div>