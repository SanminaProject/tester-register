<div>
   <livewire:components.data-table
        type="tester-audit-logs"
        title="Tester Audit Logs"
        searchPlaceholder="Search audit logs..."
        :search="(string) request('tester_id', '')"
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'changed_at' => 'Changed At',
            'tester.id' => 'Tester ID',
            'tester.name' => 'Tester Name',
            'explanation' => 'Action Details',
            'user.name' => 'User',
            'user.email' => 'Email'
        ]"
    />
</div>