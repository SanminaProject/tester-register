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
        $this->role = Role::findOrFail($roleId);
    }

    public function deleteRole()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $roleId = $this->role->id;
        $roleName = $this->role->name;

        DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted role [ID: {$roleId}] - Name: {$roleName}",
            'user_id' => auth()->id() ?? 1,
        ]);

        $this->role->delete();
        session()->flash('message', 'Role deleted successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function render()
    {
        return view('livewire.pages.admin.roles.role-details');
    }
}
