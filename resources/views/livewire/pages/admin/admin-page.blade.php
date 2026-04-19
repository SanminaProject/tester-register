<div class="flex w-full">
    <x-sidebar
        class="{{ $activeTab === 'details' ? 'hidden md:block' : '' }}"
        title="Admin"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Personnel', 'tab' => 'personnel'], // table of all personnel (edit/delete their roles in details)
            ['label' => 'Roles', 'tab' => 'roles'], // table of all roles (edit/delete roles in details)
            ['label' => 'Add New Role', 'tab' => 'add'], // form to add new role
        ]" />

    <div class="flex-1  min-w-0 px-6 py-3">
        @if ($activeTab === 'personnel')
        <livewire:pages.admin.personnel.personnel-table />
        @elseif ($activeTab === 'roles')
        <livewire:pages.admin.roles.roles-table />
        @elseif ($activeTab === 'add')
        <livewire:pages.admin.roles.role-logging />
        @elseif ($activeTab === 'personnel-details')
        <livewire:pages.admin.personnel.personnel-details :userId="$selectedUserId" wire:key="personnel-details-{{ $selectedUserId }}" />
        @elseif ($activeTab === 'role-details')
        <livewire:pages.admin.roles.role-details :roleId="$selectedRoleId" wire:key="role-details-{{ $selectedRoleId }}" />
        @elseif ($activeTab === 'edit')
        <livewire:pages.admin.roles.role-logging :roleId="$selectedRoleId" wire:key="role-edit-{{ $selectedRoleId }}" />
        @endif
    </div>
</div>