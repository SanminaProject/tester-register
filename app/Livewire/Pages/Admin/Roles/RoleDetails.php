<?php

namespace App\Livewire\Pages\Admin\Roles;

use Spatie\Permission\Models\Role;
use Livewire\Component;
use App\Models\DataChangeLog;

class RoleDetails extends Component
{
    public Role $role;

    public function mount($roleId)
    {
        $this->role = Role::withCount('users')->findOrFail($roleId);
    }

    public function deleteRole()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $this->role->delete();
        session()->flash('message', 'Role deleted successfully.');
        $this->dispatch('switchTab', tab: 'roles');
    }

    public function updateUser()
    {
        if ($this->isEdit) {
            $role = Role::findOrFail($this->roleId);

            $this->form->update($role);

            session()->flash('success', 'Role updated successfully!');
        } else {
            $this->form->save();

            session()->flash('success', 'Role created successfully!');
        }

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'roles');
    }

    public function render()
    {
        return view('livewire.pages.admin.roles.role-details');
    }
}
