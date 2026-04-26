<?php

namespace Tests\Feature\Admin;

use App\Livewire\Pages\Admin\AdminPage;
use App\Livewire\Pages\Admin\Roles\RoleDetails;
use App\Livewire\Pages\Admin\Roles\RoleLogging;
use App\Livewire\Pages\Admin\Roles\RolesTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->managerRole = Role::create(['name' => 'Manager']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole($this->adminRole);
    }

    public function test_roles_table_is_rendered_on_admin_page(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AdminPage::class)
            ->set('activeTab', 'roles')
            ->assertSeeLivewire(RolesTable::class);
    }

    public function test_roles_table_displays_roles(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RolesTable::class)
            ->assertSee('Roles List')
            ->assertSee('Manager');
    }

    public function test_role_details_component_loads_role(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoleDetails::class, ['roleId' => $this->managerRole->id])
            ->assertSet('role.id', $this->managerRole->id)
            ->assertSee('Manager')
            ->assertSee('web');
    }

    public function test_admin_can_create_role(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoleLogging::class)
            ->set('form.name', 'Supervisor')
            ->call('save')
            ->assertDispatched('saved')
            ->assertDispatched('switchTab', tab: 'roles');

        $this->assertDatabaseHas('roles', [
            'name' => 'Supervisor',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_update_role(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoleLogging::class, ['roleId' => $this->managerRole->id])
            ->set('form.name', 'Maintenance Technician')
            ->call('save')
            ->assertDispatched('saved')
            ->assertDispatched('switchTab', tab: 'roles');

        $this->assertDatabaseHas('roles', [
            'id' => $this->managerRole->id,
            'name' => 'Maintenance Technician',
        ]);
    }

    public function test_admin_can_delete_role(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoleDetails::class, ['roleId' => $this->managerRole->id])
            ->call('deleteRole')
            ->assertDispatched('switchTab', tab: 'roles');

        $this->assertDatabaseMissing('roles', [
            'id' => $this->managerRole->id,
        ]);
    }

    public function test_non_admin_cannot_delete_role(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(RoleDetails::class, ['roleId' => $this->managerRole->id])
            ->call('deleteRole')
            ->assertForbidden();
    }

    public function test_role_name_must_be_unique(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoleLogging::class)
            ->set('form.name', 'Admin')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique']);
    }
}