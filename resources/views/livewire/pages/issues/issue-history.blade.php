<div>
    <livewire:components.data-table
        type="issue-history"
        title="Issue History"
        searchPlaceholder="Search issue history..."
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