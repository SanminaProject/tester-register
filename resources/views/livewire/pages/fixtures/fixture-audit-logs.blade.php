<div>
   <livewire:components.data-table
        type="fixture-audit-logs"
        title="Fixture Audit Logs"
        searchPlaceholder="Search audit logs..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'changed_at' => 'Changed At',
            'fixture.id' => 'Fixture ID',
            'fixture.name' => 'Fixture Name',
            'explanation' => 'Action Details',
            'user.name' => 'User',
            'user.email' => 'Email'
        ]"
    />
</div>