<?php

namespace Tests\Feature\Api;

use App\Models\MaintenanceSchedule;
use App\Models\Tester;
use App\Models\TesterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenanceScheduleApiTest extends TestCase
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

    private function createTesterWithCustomer(): Tester
    {
        $customer = TesterCustomer::factory()->create();
        return Tester::factory()->create(['customer_id' => $customer->id]);
    }

    private function createTestSchedule(): MaintenanceSchedule
    {
        $tester = $this->createTesterWithCustomer();
        return MaintenanceSchedule::factory()->create(['tester_id' => $tester->id]);
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_maintenance_schedules(): void
    {
        $this->getJson('/api/v1/maintenance-schedules')
            ->assertUnauthorized();
    }

    public function test_guest_cannot_list_maintenance_schedules(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $this->createTestSchedule();

        $response = $this->getJson('/api/v1/maintenance-schedules');

        $response->assertForbidden();
    }

    public function test_technician_can_list_maintenance_schedules(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);

        $this->createTestSchedule();

        $response = $this->getJson('/api/v1/maintenance-schedules');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'tester_id', 'scheduled_date', 'status']
                    ],
                    'pagination'
                ]
            ]);
    }

    public function test_can_filter_maintenance_schedules_by_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        MaintenanceSchedule::factory()->create(['tester_id' => $tester->id, 'status' => 'pending']);
        MaintenanceSchedule::factory()->create(['tester_id' => $tester->id, 'status' => 'completed']);

        $response = $this->getJson('/api/v1/maintenance-schedules?status=pending');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.status', 'pending');
    }

    public function test_can_filter_maintenance_schedules_by_date_range(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        MaintenanceSchedule::factory()->create([
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-04-01'
        ]);
        MaintenanceSchedule::factory()->create([
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-05-01'
        ]);

        $response = $this->getJson('/api/v1/maintenance-schedules?start_date=2026-04-01&end_date=2026-04-30');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_admin_can_create_maintenance_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/maintenance-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-05-01',
            'procedure' => 'Regular maintenance check',
            'notes' => 'Check all connections',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('maintenance_schedules', [
            'tester_id' => $tester->id,
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_create_maintenance_schedule(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/maintenance-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-05-01',
            'procedure' => 'Test',
        ]);

        $response->assertForbidden();
    }

    public function test_create_schedule_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/maintenance-schedules', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tester_id',
                'scheduled_date',
                'procedure',
            ]);
    }

    public function test_create_schedule_validates_future_date(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/maintenance-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2025-01-01', // Past date
            'procedure' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_date']);
    }

    public function test_create_schedule_validates_procedure_min_length(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/maintenance-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-05-01',
            'procedure' => 'ab', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['procedure']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_maintenance_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->getJson("/api/v1/maintenance-schedules/{$schedule->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $schedule->id);
    }

    // ==================== UPDATE ENDPOINT TESTS ====================

    public function test_technician_can_update_maintenance_schedule(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->putJson("/api/v1/maintenance-schedules/{$schedule->id}", [
            'procedure' => 'Updated procedure',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.procedure', 'Updated procedure');

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'procedure' => 'Updated procedure',
        ]);
    }

    public function test_guest_cannot_update_maintenance_schedule(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $schedule = $this->createTestSchedule();

        $response = $this->putJson("/api/v1/maintenance-schedules/{$schedule->id}", [
            'procedure' => 'Updated',
        ]);

        $response->assertForbidden();
    }

    // ==================== COMPLETE ENDPOINT TESTS ====================

    public function test_technician_can_complete_maintenance(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->postJson("/api/v1/maintenance-schedules/{$schedule->id}/complete", [
            'completed_date' => '2026-04-02',
            'performed_by' => 'John Doe',
            'notes' => 'Maintenance completed successfully',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.performed_by', 'John Doe');

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'completed',
        ]);
    }

    public function test_complete_validates_date_not_future(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->postJson("/api/v1/maintenance-schedules/{$schedule->id}/complete", [
            'completed_date' => '2026-12-31', // Future date
            'performed_by' => 'John',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed_date']);
    }

    public function test_complete_validates_performed_by(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->postJson("/api/v1/maintenance-schedules/{$schedule->id}/complete", [
            'completed_date' => '2026-04-02',
            'performed_by' => 'A', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['performed_by']);
    }

    // ==================== DELETE ENDPOINT TESTS ====================

    public function test_admin_can_delete_maintenance_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->deleteJson("/api/v1/maintenance-schedules/{$schedule->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('maintenance_schedules', ['id' => $schedule->id]);
    }

    public function test_technician_cannot_delete_maintenance_schedule(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->deleteJson("/api/v1/maintenance-schedules/{$schedule->id}");

        $response->assertForbidden();
    }
}
