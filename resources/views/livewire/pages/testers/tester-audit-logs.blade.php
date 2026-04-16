<div>
   <livewire:components.data-table
        type="tester-audit-logs"
        title="Tester Audit Logs"
        searchPlaceholder="Search audit logs..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'explanation' => 'Explanation',
            'changed_at' => 'Changed At',
            'tester.id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'user.first_name' => 'Firstname',
            'user.last_name' => 'Lastname',
            'user.email' => 'Email'
        ]"
    />
</div>