<?php

namespace Tests\Feature\Api;

use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FixtureApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createRoles(): void
    {
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Maintenance Technician']);
        Role::firstOrCreate(['name' => 'Calibration Specialist']);
        Role::firstOrCreate(['name' => 'Guest']);
    }

    private function createAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        return $user;
    }

    private function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Maintenance Technician');
        return $user;
    }

    private function createTesterWithCustomer(): Tester
    {
        $customer = TesterCustomer::factory()->create();
        return Tester::factory()->create(['customer_id' => $customer->id]);
    }

    private function createTestFixture(): Fixture
    {
        $tester = $this->createTesterWithCustomer();
        return Fixture::factory()->create(['tester_id' => $tester->id]);
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_fixtures(): void
    {
        $this->getJson('/api/v1/fixtures')
            ->assertUnauthorized();
    }

    public function test_can_list_fixtures_with_pagination(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        for ($i = 0; $i < 10; $i++) {
            $this->createTestFixture();
        }

        $response = $this->getJson('/api/v1/fixtures?per_page=5');

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.total', 10)
            ->assertJsonCount(5, 'data.items');
    }

    public function test_can_filter_fixtures_by_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester1 = $this->createTesterWithCustomer();
        $tester2 = $this->createTesterWithCustomer();

        Fixture::factory()->create(['tester_id' => $tester1->id]);
        Fixture::factory()->create(['tester_id' => $tester2->id]);

        $response = $this->getJson("/api/v1/fixtures?tester_id={$tester1->id}");

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.tester_id', $tester1->id);
    }

    public function test_can_filter_fixtures_by_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        Fixture::factory()->create(['tester_id' => $tester->id, 'status' => 'active']);
        Fixture::factory()->create(['tester_id' => $tester->id, 'status' => 'inactive']);

        $response = $this->getJson('/api/v1/fixtures?status=active');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.status', 'active');
    }

    public function test_can_search_fixtures(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        Fixture::factory()->create([
            'tester_id' => $tester->id,
            'name' => 'Fixture Alpha',
            'serial_number' => 'FIX-001'
        ]);
        Fixture::factory()->create([
            'tester_id' => $tester->id,
            'name' => 'Fixture Beta',
            'serial_number' => 'FIX-002'
        ]);

        $response = $this->getJson('/api/v1/fixtures?search=Alpha');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.name', 'Fixture Alpha');
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_admin_can_create_fixture(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/fixtures', [
            'name' => 'Test Fixture',
            'serial_number' => 'FIX-NEW-001',
            'tester_id' => $tester->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Test Fixture')
            ->assertJsonPath('data.serial_number', 'FIX-NEW-001');

        $this->assertDatabaseHas('fixtures', [
            'name' => 'Test Fixture',
            'serial_number' => 'FIX-NEW-001',
        ]);
    }

    public function test_guest_cannot_create_fixture(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/fixtures', [
            'name' => 'Test Fixture',
            'serial_number' => 'FIX-NEW',
            'tester_id' => $tester->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_create_fixture_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/fixtures', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'serial_number',
                'tester_id',
                'purchase_date',
                'status',
            ]);
    }

    public function test_create_fixture_validates_unique_serial_number(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $fixture = $this->createTestFixture();
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/fixtures', [
            'name' => 'Test Fixture',
            'serial_number' => $fixture->serial_number, // Duplicate
            'tester_id' => $tester->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number']);
    }

    public function test_create_fixture_validates_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/fixtures', [
            'name' => 'Test',
            'serial_number' => 'FIX-123',
            'tester_id' => $tester->id,
            'purchase_date' => '2026-01-15',
            'status' => 'unknown',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_fixture_details(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $fixture = $this->createTestFixture();

        $response = $this->getJson("/api/v1/fixtures/{$fixture->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $fixture->id)
            ->assertJsonPath('data.name', $fixture->name);
    }

    public function test_show_nonexistent_fixture_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/fixtures/9999');

        $response->assertNotFound();
    }

    // ==================== UPDATE ENDPOINT TESTS ====================

    public function test_admin_can_update_fixture(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $fixture = $this->createTestFixture();

        $response = $this->putJson("/api/v1/fixtures/{$fixture->id}", [
            'name' => 'Updated Fixture',
            'status' => 'inactive',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Fixture')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('fixtures', [
            'id' => $fixture->id,
            'name' => 'Updated Fixture',
        ]);
    }

    public function test_guest_cannot_update_fixture(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $fixture = $this->createTestFixture();

        $response = $this->putJson("/api/v1/fixtures/{$fixture->id}", [
            'name' => 'Updated',
        ]);

        $response->assertForbidden();
    }

    public function test_update_fixture_validates_serial_number_unique(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $fixture1 = $this->createTestFixture();
        $fixture2 = $this->createTestFixture();

        $response = $this->putJson("/api/v1/fixtures/{$fixture1->id}", [
            'serial_number' => $fixture2->serial_number,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number']);
    }

    // ==================== DELETE ENDPOINT TESTS ====================

    public function test_admin_can_delete_fixture(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $fixture = $this->createTestFixture();

        $response = $this->deleteJson("/api/v1/fixtures/{$fixture->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('fixtures', ['id' => $fixture->id]);
    }

    public function test_non_admin_cannot_delete_fixture(): void
    {
        $manager = $this->createManagerUser();
        Sanctum::actingAs($manager);
        $fixture = $this->createTestFixture();

        $response = $this->deleteJson("/api/v1/fixtures/{$fixture->id}");

        $response->assertForbidden();
    }
}
