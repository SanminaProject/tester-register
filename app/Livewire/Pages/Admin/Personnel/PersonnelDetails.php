<?php

namespace App\Livewire\Pages\Admin\Personnel;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PersonnelDetails extends Component
{
    public User $user;

    public $roles;
    public $editing = false;

    public $selectedRoleName = null;

    public function mount($userId)
    {
        $this->user = User::with('roles')->findOrFail($userId);
        $this->roles = Role::orderBy('name')->get();

        $this->selectedRoleName = $this->user->roles->first()?->name;
    }

    public function enableEdit()
    {
        $this->editing = true;
    }

    public function updatePersonnelRole()
    {
        if (!$this->selectedRoleName) return;

        $this->user->syncRoles([$this->selectedRoleName]);

        $this->user->load('roles');

        $this->editing = false;
    }

    public function removePersonnelRole()
    {
        if (!$this->selectedRoleName) return;

        $this->user->removeRole($this->selectedRoleName);

        $this->user->load('roles');
        $this->selectedRoleName = null;
        $this->editing = false;
    }

    public function createRoleOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $role = Role::firstOrCreate(['name' => $value, 'guard_name' => 'web']);
        $this->roles = Role::orderBy('name')->get();
        $this->selectedRoleName = $role->name;

        $this->dispatch('dropdown-option-created',
            optionId: $role->name,
            optionLabel: $role->name,
            createMethod: 'createRoleOption'
        );
    }

    public function deleteRoleOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $role = Role::where('name', $value)->first();
        if (! $role instanceof Role) {
            return;
        }

        if (User::role($value)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteRoleOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $role->delete();
        $this->roles = Role::orderBy('name')->get();

        if ((string) $this->selectedRoleName === $value) {
            $this->selectedRoleName = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: $value,
            deleteMethod: 'deleteRoleOption'
        );
    }

    public function deletePersonnel()
    {
        // do not delete the last remaining admin
        if ($this->user->hasRole('Admin') && User::role('Admin')->count() <= 1) {
            return;
        }

        // user cannot delete themselves (via this page)
        if (auth()->id() === $this->user->id) {
            return;
        }

        $this->user->delete();

        $this->dispatch('switchTab', tab: 'personnel');
    }

    public function render()
    {
        return view('livewire.pages.admin.personnel.personnel-details');
    }
}