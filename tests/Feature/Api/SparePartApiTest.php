<?php

namespace Tests\Feature\Api;

use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SparePartApiTest extends TestCase
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

    private function createTestPart(): SparePart
    {
        return SparePart::factory()->create();
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_spare_parts(): void
    {
        $this->getJson('/api/v1/spare-parts')
            ->assertUnauthorized();
    }

    public function test_guest_cannot_list_spare_parts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $this->createTestPart();

        $response = $this->getJson('/api/v1/spare-parts');

        $response->assertForbidden();
    }

    public function test_technician_can_list_spare_parts(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);

        $this->createTestPart();

        $response = $this->getJson('/api/v1/spare-parts');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'part_number', 'quantity_in_stock']
                    ],
                    'pagination'
                ]
            ]);
    }

    public function test_can_filter_spare_parts_by_stock_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        SparePart::factory()->create(['quantity_in_stock' => 3]);     // low
        SparePart::factory()->create(['quantity_in_stock' => 10]);    // normal
        SparePart::factory()->create(['quantity_in_stock' => 25]);    // full

        $response = $this->getJson('/api/v1/spare-parts?stock_status=low');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_can_search_spare_parts(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        SparePart::factory()->create([
            'name' => 'Capacitor 100uF',
            'part_number' => 'CAP-100'
        ]);
        SparePart::factory()->create([
            'name' => 'Resistor 10k',
            'part_number' => 'RES-10K'
        ]);

        $response = $this->getJson('/api/v1/spare-parts?search=Capacitor');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.name', 'Capacitor 100uF');
    }

    public function test_can_list_spare_parts_with_pagination(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        for ($i = 0; $i < 20; $i++) {
            $this->createTestPart();
        }

        $response = $this->getJson('/api/v1/spare-parts?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 20)
            ->assertJsonCount(10, 'data.items');
    }

    public function test_list_spare_parts_validates_pagination_parameters(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/spare-parts?page=0&per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['page', 'per_page']);
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_admin_can_create_spare_part(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/spare-parts', [
            'name' => 'PCB Board X100',
            'part_number' => 'PCB-X100',
            'quantity_in_stock' => 5,
            'unit_cost' => 45.50,
            'supplier' => 'ElectroSupply Inc',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'PCB Board X100');

        $this->assertDatabaseHas('spare_parts', [
            'part_number' => 'PCB-X100',
        ]);
    }

    public function test_guest_cannot_create_spare_part(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/spare-parts', [
            'name' => 'Test Part',
            'part_number' => 'TEST-001',
            'quantity_in_stock' => 5,
            'unit_cost' => 10.00,
        ]);

        $response->assertForbidden();
    }

    public function test_create_spare_part_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/spare-parts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'part_number',
                'quantity_in_stock',
                'unit_cost',
            ]);
    }

    public function test_create_spare_part_validates_unique_part_number(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $part = $this->createTestPart();

        $response = $this->postJson('/api/v1/spare-parts', [
            'name' => 'Different Name',
            'part_number' => $part->part_number, // Duplicate
            'quantity_in_stock' => 5,
            'unit_cost' => 10.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['part_number']);
    }

    public function test_create_spare_part_validates_quantity_non_negative(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/spare-parts', [
            'name' => 'Test Part',
            'part_number' => 'TEST-NEW',
            'quantity_in_stock' => -5, // Negative
            'unit_cost' => 10.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity_in_stock']);
    }

    public function test_create_spare_part_validates_cost_range(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/spare-parts', [
            'name' => 'Expensive Part',
            'part_number' => 'EXP-001',
            'quantity_in_stock' => 1,
            'unit_cost' => 9999999.99, // Exceeds max
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_cost']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_spare_part_details(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $part = $this->createTestPart();

        $response = $this->getJson("/api/v1/spare-parts/{$part->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $part->id)
            ->assertJsonPath('data.name', $part->name);
    }

    public function test_show_nonexistent_spare_part_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/spare-parts/9999');

        $response->assertNotFound();
    }

    // ==================== UPDATE ENDPOINT TESTS ====================

    public function test_admin_can_update_spare_part(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $part = $this->createTestPart();

        $response = $this->putJson("/api/v1/spare-parts/{$part->id}", [
            'quantity_in_stock' => 20,
            'unit_cost' => 55.75,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.quantity_in_stock', 20)
            ->assertJsonPath('data.unit_cost', '55.75');

        $this->assertDatabaseHas('spare_parts', [
            'id' => $part->id,
            'quantity_in_stock' => 20,
        ]);
    }

    public function test_guest_cannot_update_spare_part(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $part = $this->createTestPart();

        $response = $this->putJson("/api/v1/spare-parts/{$part->id}", [
            'quantity_in_stock' => 20,
        ]);

        $response->assertForbidden();
    }

    public function test_update_spare_part_validates_part_number_unique(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $part1 = $this->createTestPart();
        $part2 = $this->createTestPart();

        $response = $this->putJson("/api/v1/spare-parts/{$part1->id}", [
            'part_number' => $part2->part_number,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['part_number']);
    }

    public function test_update_spare_part_allows_self_reference(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $part = $this->createTestPart();

        $response = $this->putJson("/api/v1/spare-parts/{$part->id}", [
            'part_number' => $part->part_number, // Same part number is ok
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    // ==================== DELETE ENDPOINT TESTS ====================

    public function test_admin_can_delete_spare_part(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $part = $this->createTestPart();

        $response = $this->deleteJson("/api/v1/spare-parts/{$part->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('spare_parts', ['id' => $part->id]);
    }

    public function test_technician_cannot_delete_spare_part(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $part = $this->createTestPart();

        $response = $this->deleteJson("/api/v1/spare-parts/{$part->id}");

        $response->assertForbidden();
    }

    public function test_delete_nonexistent_spare_part_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/v1/spare-parts/9999');

        $response->assertNotFound();
    }
}
