<?php

namespace App\Livewire\Pages\Admin\Roles;

use Livewire\Component;
use App\Livewire\Forms\RoleForm;
use Spatie\Permission\Models\Role;

class RoleLogging extends Component
{
    public RoleForm $form;

    public ?int $roleId = null;
    public bool $isEdit = false;

    public function mount($roleId = null)
    {
        if ($roleId) {
            $this->roleId = $roleId;
            $this->isEdit = true;

            $role = Role::findOrFail($roleId);

            // fill form with existing data
            $this->form->name = $role->name;
            $this->form->guard_name = $role->guard_name;
        }
    }

    public function save()
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
        return view('livewire.pages.admin.roles.role-logging');
    }
}