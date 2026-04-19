<div>
   <livewire:components.data-table
        type="roles"
        title="Roles List"
        searchPlaceholder="Search roles..."
        addButtonLabel="Add Roles"
        :headers="[
            'id' => 'ID',
            'name' => 'Role Name',
            'guard_name' => 'Guard',
            'users_count' => 'Users',
            'created_at' => 'Created At',
        ]"
    />
</div>