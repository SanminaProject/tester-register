<div>
   <livewire:components.data-table
        type="inventory-audit-logs"
        title="Inventory Audit Logs"
        searchPlaceholder="Search logs..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'changed_at' => 'Changed At',
            'spare_part.id' => 'Spare Part ID',
            'spare_part.name' => 'Spare Part Name',
            'explanation' => 'Action Details',
            'user.name' => 'User',
            'user.email' => 'Email'
        ]"
    />
</div>