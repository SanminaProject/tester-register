<?php

namespace Tests\Feature\Admin;

use App\Livewire\Pages\Admin\AdminPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Livewire\Pages\Admin\Personnel\PersonnelTable;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
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

    public function test_admin_page_is_displayed_for_admin_users(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(AdminPage::class);
    }

    public function test_admin_page_is_not_displayed_for_non_admin_users(): void
    {
        $this->actingAs($this->normalUser)
            ->get('/admin')
            ->assertForbidden();
    }
}