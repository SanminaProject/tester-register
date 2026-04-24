<div>
   <livewire:components.data-table
        type="inventory-audit-logs"
        title="Inventory Audit Logs"
        searchPlaceholder="Search logs..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'type' => 'Type',
            'changed_at' => 'Changed At',
            'entity_id' => 'ID', // show either part ID or supplier ID based on type
            'entity_name' => 'Name', // show either part name or supplier name based on type
            'explanation' => 'Action Details',
            'user.name' => 'User',
            'user.email' => 'Email'
        ]"
    />
</div>