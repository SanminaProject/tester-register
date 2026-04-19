<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class RoleForm extends Form
{
    public string $name = '';
    public string $guard_name = 'web'; 

     protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
        ];
    }

    public function save()
    {
        $this->validate();

        Role::create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        $this->reset();
    }

    public function update(Role $role)
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'guard_name' => 'required|string',
        ]);

        $role->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);
    }
}