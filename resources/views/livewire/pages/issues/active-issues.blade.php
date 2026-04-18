<div>
    <livewire:components.data-table
        type="issues"
        title="Active Issues"
        searchPlaceholder="Search issues..."
        addButtonLabel="Add Issue"
        :headers="[
            'date' => 'Date',
            'tester_id' => 'Test ID',
            'description' => 'Problem',
            'createdBy.email' => 'User',
            'issueStatusRelation.name' => 'Status',
        ]" />
</div>