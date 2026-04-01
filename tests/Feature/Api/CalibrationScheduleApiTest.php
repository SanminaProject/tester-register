<?php

namespace Tests\Feature\Api;

use App\Models\CalibrationSchedule;
use App\Models\Tester;
use App\Models\TesterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalibrationScheduleApiTest extends TestCase
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

    private function createTestSchedule(): CalibrationSchedule
    {
        $tester = $this->createTesterWithCustomer();
        return CalibrationSchedule::factory()->create(['tester_id' => $tester->id]);
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_calibration_schedules(): void
    {
        $this->getJson('/api/v1/calibration-schedules')
            ->assertUnauthorized();
    }

    public function test_guest_cannot_list_calibration_schedules(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $this->createTestSchedule();

        $response = $this->getJson('/api/v1/calibration-schedules');

        $response->assertForbidden();
    }

    public function test_technician_can_list_calibration_schedules(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);

        $this->createTestSchedule();

        $response = $this->getJson('/api/v1/calibration-schedules');

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

    public function test_can_filter_calibration_schedules_by_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        CalibrationSchedule::factory()->create(['tester_id' => $tester->id, 'status' => 'pending']);
        CalibrationSchedule::factory()->create(['tester_id' => $tester->id, 'status' => 'completed']);

        $response = $this->getJson('/api/v1/calibration-schedules?status=pending');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.status', 'pending');
    }

    public function test_can_filter_calibration_schedules_by_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester1 = $this->createTesterWithCustomer();
        $tester2 = $this->createTesterWithCustomer();

        CalibrationSchedule::factory()->create(['tester_id' => $tester1->id]);
        CalibrationSchedule::factory()->create(['tester_id' => $tester2->id]);

        $response = $this->getJson("/api/v1/calibration-schedules?tester_id={$tester1->id}");

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.tester_id', $tester1->id);
    }

    public function test_list_calibration_schedules_validates_pagination_parameters(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/calibration-schedules?page=0&per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['page', 'per_page']);
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_admin_can_create_calibration_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/calibration-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-06-01',
            'procedure' => 'Standard calibration',
            'notes' => 'Annual calibration',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('calibration_schedules', [
            'tester_id' => $tester->id,
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_create_calibration_schedule(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/calibration-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2026-06-01',
            'procedure' => 'Test',
        ]);

        $response->assertForbidden();
    }

    public function test_create_schedule_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/calibration-schedules', []);

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

        $response = $this->postJson('/api/v1/calibration-schedules', [
            'tester_id' => $tester->id,
            'scheduled_date' => '2025-01-01',
            'procedure' => 'Test procedure',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_date']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_calibration_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->getJson("/api/v1/calibration-schedules/{$schedule->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $schedule->id);
    }

    // ==================== UPDATE ENDPOINT TESTS ====================

    public function test_technician_can_update_calibration_schedule(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->putJson("/api/v1/calibration-schedules/{$schedule->id}", [
            'procedure' => 'Updated calibration procedure',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.procedure', 'Updated calibration procedure');
    }

    public function test_guest_cannot_update_calibration_schedule(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $schedule = $this->createTestSchedule();

        $response = $this->putJson("/api/v1/calibration-schedules/{$schedule->id}", [
            'procedure' => 'Updated',
        ]);

        $response->assertForbidden();
    }

    // ==================== COMPLETE ENDPOINT TESTS ====================

    public function test_technician_can_complete_calibration(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->postJson("/api/v1/calibration-schedules/{$schedule->id}/complete", [
            'completed_date' => '2026-04-01',
            'performed_by' => 'Jane Smith',
            'notes' => 'Calibration completed',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.performed_by', 'Jane Smith');

        $this->assertDatabaseHas('calibration_schedules', [
            'id' => $schedule->id,
            'status' => 'completed',
        ]);
    }

    public function test_complete_validates_date_not_future(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->postJson("/api/v1/calibration-schedules/{$schedule->id}/complete", [
            'completed_date' => '2026-12-31',
            'performed_by' => 'Jane',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed_date']);
    }

    // ==================== DELETE ENDPOINT TESTS ====================

    public function test_admin_can_delete_calibration_schedule(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $schedule = $this->createTestSchedule();

        $response = $this->deleteJson("/api/v1/calibration-schedules/{$schedule->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('calibration_schedules', ['id' => $schedule->id]);
    }

    public function test_technician_cannot_delete_calibration_schedule(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $schedule = $this->createTestSchedule();

        $response = $this->deleteJson("/api/v1/calibration-schedules/{$schedule->id}");

        $response->assertForbidden();
    }
}
