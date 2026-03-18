<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserRoles extends Component
{
    public $users;
    public $roles;

    public $selectedUserId = null;
    public $selectedRoleName = null;
    public $selectedUser = null;

    public function mount()
    {
        $this->users = User::with('roles')->get();
        $this->roles = Role::all();
    }

    public function selectUser()
    {
        $this->selectedUser = User::with('roles')->find($this->selectedUserId);
        $this->selectedRoleName = $this->selectedUser?->roles?->first()?->name;
    }

    public function updateUserRole()
    {
        if (!$this->selectedUserId || !$this->selectedRoleName) {
            return;
        }

        $user = User::find($this->selectedUserId);

        if ($user) {
            $user->syncRoles([$this->selectedRoleName]);

            // refresh selected user
            $this->selectedUser = $user->load('roles');

            // refresh user list
            $this->users = User::with('roles')->get();
        }
    }

    public function removeUserRole()
    {
        if (!$this->selectedUserId || !$this->selectedRoleName) {
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user) {
            return;
        }

        $user->removeRole($this->selectedRoleName);

        $this->selectedUser = $user->load('roles');
        $this->users = User::with('roles')->get();
    }

    public function render()
    {
        return view('livewire.pages.admin.user-roles')->layout('layouts.app');
    }
}