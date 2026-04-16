<div>
   <livewire:components.data-table
        type="fixture-audit-logs"
        title="Fixture Audit Logs"
        searchPlaceholder="Search audit logs..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'explanation' => 'Explanation',
            'changed_at' => 'Changed At',
            'fixture.id' => 'Fixture ID',
            'fixture.name' => 'Fixture Name',
            'user.first_name' => 'Firstname',
            'user.last_name' => 'Lastname',
            'user.email' => 'Email'
        ]"
    />
</div>