<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Livewire\Pages\Admin\EditUserRoles;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;


class UserRolesTest extends TestCase
{
    use RefreshDatabase;
    protected User $adminUser;
    protected User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole($this->adminRole);
    }
    

    // -- Access tests --
    public function test_user_roles_page_is_displayed_for_admin_users(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/user-roles')
            ->assertOk();
    }

    public function test_user_roles_page_is_not_displayed_for_non_admin_users(): void
    {
        $this->actingAs($this->normalUser)
            ->get('/user-roles')
            ->assertForbidden();
    }


    // -- Initial UI state tests --
    public function test_users_dropdown_is_visible(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->assertSee('Select User')
            ->assertSee($this->normalUser->email);
    }

    public function test_user_details_are_not_visible_initially(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->assertDontSee('User Details');
    }

    public function test_roles_assigning_is_not_visible_initially(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->assertDontSee('Assign Role');
    }


    // -- After selecting a user tests --
    public function test_user_details_are_visible_after_selection(): void 
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->set('selectedUserId', $this->normalUser->id)
            ->call('selectUser')
            ->assertSee('User Details')
            ->assertSee($this->normalUser->email);
    }

    public function test_roles_assigning_is_visible_after_selection(): void
    {
       Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->set('selectedUserId', $this->normalUser->id)
            ->call('selectUser')
            ->assertSee('Assign Role')
            ->assertSee($this->adminRole->name);
    }


    // -- Interaction tests --
    public function test_user_can_be_selected(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->set('selectedUserId', $this->normalUser->id)
            ->call('selectUser')
            ->assertSet('selectedUser.id', $this->normalUser->id);
    }

    public function test_role_can_be_updated_for_selected_user(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->set('selectedUserId', $this->normalUser->id)
            ->call('selectUser')
            ->set('selectedRoleName', 'Admin')
            ->call('updateUserRole');

        $this->assertTrue(
            $this->normalUser->fresh()->hasRole('Admin')
        );
    }

    public function test_role_can_be_removed_from_selected_user(): void
    {
        $this->normalUser->assignRole('Admin');

        Livewire::actingAs($this->adminUser)
            ->test(EditUserRoles::class)
            ->set('selectedUserId', $this->normalUser->id)
            ->call('selectUser')
            ->call('removeUserRole');

        $this->assertFalse(
            $this->normalUser->fresh()->hasRole('Admin')
        );
    }
}
