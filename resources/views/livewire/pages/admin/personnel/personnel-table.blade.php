<div>
   <livewire:components.data-table
        type="personnel"
        title="Personnel List"
        searchPlaceholder="Search personnel..."
        :showAddButton="false"
        :headers="[
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'role_names' => 'Role',
            'email' => 'Email',
            'phone' => 'Phone',
            'qualifications_certifications' => 'Qualifications Certifications',
            'responsibilities' => 'Additional Info',
            'tester_names' => 'Testers Responsible For',

        ]"
    />
</div>