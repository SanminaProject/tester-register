<div>
    <livewire:components.data-table
        type="issue-history"
        title="Issue History"
        searchPlaceholder="Search issue history..."
        :showAddButton="false"
        :headers="[
            'date' => 'Date',
            'description' => 'Action',
            'tester_id' => 'Test ID',
            'createdBy.email' => 'User',
        ]" />
</div>