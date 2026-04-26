<?php

namespace Tests\Feature\Admin;

use App\Livewire\Pages\Admin\AdminPage;
use App\Livewire\Pages\Admin\Personnel\PersonnelDetails;
use App\Livewire\Pages\Admin\Personnel\PersonnelTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PersonnelTest extends TestCase
{
    use RefreshDatabase;
    protected User $adminUser;
    protected User $normalUser;
    protected Role $adminRole;
    protected Role $managerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->managerRole = Role::create(['name' => 'Manager']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole($this->adminRole);
    }

    // is this test wrong?
    public function test_personnel_table_displays_personnel_data(): void
    {
        $person = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelTable::class)
            ->assertSee('Jane')
            ->assertSee('Doe')
            ->assertSee('jane@example.com');
    }

    public function test_personnel_details_component_displays_personnel_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->normalUser->id])
            ->assertSet('user.id', $this->normalUser->id)
            ->assertSee($this->normalUser->first_name)
            ->assertSee($this->normalUser->email);
    }

    public function test_personnel_role_can_be_updated(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->normalUser->id])
            ->set('selectedRoleName', 'Manager')
            ->call('updatePersonnelRole')
            ->assertSet('editing', false);

        $this->assertTrue($this->normalUser->fresh()->hasRole('Manager'));
    }

    public function test_personnel_role_can_be_removed(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->normalUser->id])
            ->set('selectedRoleName', 'Manager')
            ->call('removePersonnelRole')
            ->assertSet('selectedRoleName', null)
            ->assertSet('editing', false);

        $this->assertFalse($this->normalUser->fresh()->hasRole('Manager'));
    }

    public function test_admin_can_delete_personnel(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->normalUser->id])
            ->call('deletePersonnel')
            ->assertDispatched('switchTab', tab: 'personnel');

        $this->assertNull($this->normalUser->fresh());
    }

    public function test_last_admin_cannot_be_deleted(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->adminUser->id])
            ->call('deletePersonnel');

        $this->assertNotNull($this->adminUser->fresh());
    }

    public function test_user_cannot_delete_themselves(): void
    {
        // so last admin rule doesn't interfere with this test
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole($this->adminRole);

        $this->actingAs($this->adminUser);

        Livewire::test(PersonnelDetails::class, ['userId' => $this->adminUser->id])
            ->call('deletePersonnel');

        $this->assertNotNull($this->adminUser->fresh());
    }
}