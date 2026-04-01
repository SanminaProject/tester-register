<?php

namespace Tests\Feature\Api;

use App\Models\Tester;
use App\Models\TesterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TesterApiTest extends TestCase
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

    private function createTechnicianUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Calibration Specialist');
        return $user;
    }

    private function createGuestUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        return $user;
    }

    private function createTestTester(): Tester
    {
        $customer = TesterCustomer::factory()->create();
        return Tester::factory()->create(['customer_id' => $customer->id]);
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_testers(): void
    {
        $this->getJson('/api/v1/testers')
            ->assertUnauthorized();
    }

    public function test_guest_can_list_testers(): void
    {
        $guest = $this->createGuestUser();
        Sanctum::actingAs($guest);

        $this->createTestTester();
        $this->createTestTester();

        $response = $this->getJson('/api/v1/testers');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.model', fn($v) => is_string($v))
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'model', 'serial_number', 'status', 'customer_id']
                    ],
                    'pagination'
                ]
            ]);
    }

    public function test_can_list_testers_with_pagination(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        // Create 20 testers
        for ($i = 0; $i < 20; $i++) {
            $this->createTestTester();
        }

        $response = $this->getJson('/api/v1/testers?per_page=5');

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.total', 20)
            ->assertJsonPath('data.pagination.total_pages', 4)
            ->assertJsonCount(5, 'data.items');
    }

    public function test_can_filter_testers_by_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $customer = TesterCustomer::factory()->create();
        Tester::factory()->create(['customer_id' => $customer->id, 'status' => 'active']);
        Tester::factory()->create(['customer_id' => $customer->id, 'status' => 'inactive']);

        $response = $this->getJson('/api/v1/testers?status=active');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.status', 'active');
    }

    public function test_can_filter_testers_by_customer(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $customer1 = TesterCustomer::factory()->create();
        $customer2 = TesterCustomer::factory()->create();
        Tester::factory()->create(['customer_id' => $customer1->id]);
        Tester::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->getJson("/api/v1/testers?customer_id={$customer1->id}");

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.customer_id', $customer1->id);
    }

    public function test_can_search_testers(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $customer = TesterCustomer::factory()->create();
        Tester::factory()->create([
            'customer_id' => $customer->id,
            'model' => 'Model-X-500',
            'serial_number' => '12345'
        ]);
        Tester::factory()->create([
            'customer_id' => $customer->id,
            'model' => 'Model-Y-600',
            'serial_number' => '67890'
        ]);

        $response = $this->getJson('/api/v1/testers?search=Model-X');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.model', 'Model-X-500');
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_admin_can_create_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $customer = TesterCustomer::factory()->create();

        $response = $this->postJson('/api/v1/testers', [
            'model' => 'Test Model A',
            'serial_number' => 'SN-001',
            'customer_id' => $customer->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
            'location' => 'Building A',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.model', 'Test Model A')
            ->assertJsonPath('data.serial_number', 'SN-001');

        $this->assertDatabaseHas('testers', [
            'model' => 'Test Model A',
            'serial_number' => 'SN-001',
        ]);
    }

    public function test_guest_cannot_create_tester(): void
    {
        $guest = $this->createGuestUser();
        Sanctum::actingAs($guest);
        $customer = TesterCustomer::factory()->create();

        $response = $this->postJson('/api/v1/testers', [
            'model' => 'Test Model',
            'serial_number' => 'SN-002',
            'customer_id' => $customer->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_create_tester_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/testers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'model',
                'serial_number',
                'customer_id',
                'purchase_date',
            ]);
    }

    public function test_create_tester_validates_unique_serial_number(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTestTester();
        $customer = TesterCustomer::factory()->create();

        $response = $this->postJson('/api/v1/testers', [
            'model' => 'Test Model',
            'serial_number' => $tester->serial_number, // Duplicate
            'customer_id' => $customer->id,
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number']);
    }

    public function test_create_tester_validates_customer_exists(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/testers', [
            'model' => 'Test Model',
            'serial_number' => 'SN-NEW',
            'customer_id' => 9999, // Non-existent
            'purchase_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_create_tester_validates_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $customer = TesterCustomer::factory()->create();

        $response = $this->postJson('/api/v1/testers', [
            'model' => 'Test Model',
            'serial_number' => 'SN-NEW',
            'customer_id' => $customer->id,
            'purchase_date' => '2026-01-15',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_tester_details(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTestTester();

        $response = $this->getJson("/api/v1/testers/{$tester->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $tester->id)
            ->assertJsonPath('data.model', $tester->model)
            ->assertJsonStructure([
                'data' => [
                    'id', 'model', 'serial_number', 'customer_id',
                    'fixtures', 'recent_events', 'maintenance_schedules'
                ]
            ]);
    }

    public function test_show_nonexistent_tester_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/testers/9999');

        $response->assertNotFound();
    }

    // ==================== UPDATE ENDPOINT TESTS ====================

    public function test_admin_can_update_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTestTester();

        $response = $this->putJson("/api/v1/testers/{$tester->id}", [
            'model' => 'Updated Model',
            'location' => 'New Building',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.model', 'Updated Model')
            ->assertJsonPath('data.location', 'New Building');

        $this->assertDatabaseHas('testers', [
            'id' => $tester->id,
            'model' => 'Updated Model',
        ]);
    }

    public function test_guest_cannot_update_tester(): void
    {
        $guest = $this->createGuestUser();
        Sanctum::actingAs($guest);
        $tester = $this->createTestTester();

        $response = $this->putJson("/api/v1/testers/{$tester->id}", [
            'model' => 'Updated Model',
        ]);

        $response->assertForbidden();
    }

    public function test_update_tester_validates_serial_number_unique(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester1 = $this->createTestTester();
        $tester2 = $this->createTestTester();

        $response = $this->putJson("/api/v1/testers/{$tester1->id}", [
            'serial_number' => $tester2->serial_number, // Duplicate
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number']);
    }

    // ==================== UPDATE STATUS TESTS ====================

    public function test_can_update_tester_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTestTester();

        $response = $this->patchJson("/api/v1/testers/{$tester->id}/status", [
            'status' => 'maintenance',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'maintenance');

        $this->assertDatabaseHas('testers', [
            'id' => $tester->id,
            'status' => 'maintenance',
        ]);
    }

    public function test_update_status_validates_status_value(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTestTester();

        $response = $this->patchJson("/api/v1/testers/{$tester->id}/status", [
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ==================== DELETE ENDPOINT TESTS ====================

    public function test_admin_can_delete_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTestTester();

        $response = $this->deleteJson("/api/v1/testers/{$tester->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('testers', ['id' => $tester->id]);
    }

    public function test_non_admin_cannot_delete_tester(): void
    {
        $manager = $this->createManagerUser();
        Sanctum::actingAs($manager);
        $tester = $this->createTestTester();

        $response = $this->deleteJson("/api/v1/testers/{$tester->id}");

        $response->assertForbidden();
    }

    public function test_delete_nonexistent_tester_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/v1/testers/9999');

        $response->assertNotFound();
    }
}
