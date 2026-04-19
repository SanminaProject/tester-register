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
            'responsibilities' => 'Responsibilities',
            'tester_names' => 'Testers Responsible For',
            'qualifications_certifications' => 'Qualifications Certifications',
        ]"
    />
</div>