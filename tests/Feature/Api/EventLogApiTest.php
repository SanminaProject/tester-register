<?php

namespace Tests\Feature\Api;

use App\Models\EventLog;
use App\Models\Tester;
use App\Models\TesterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventLogApiTest extends TestCase
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

    private function createTestLog(): EventLog
    {
        $tester = $this->createTesterWithCustomer();
        return EventLog::factory()->create(['tester_id' => $tester->id]);
    }

    // ==================== LIST ENDPOINT TESTS ====================

    public function test_unauthenticated_user_cannot_list_event_logs(): void
    {
        $this->getJson('/api/v1/event-logs')
            ->assertUnauthorized();
    }

    public function test_guest_cannot_list_event_logs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);

        $this->createTestLog();

        $response = $this->getJson('/api/v1/event-logs');

        $response->assertForbidden();
    }

    public function test_technician_can_list_event_logs(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);

        $this->createTestLog();

        $response = $this->getJson('/api/v1/event-logs');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'tester_id', 'type', 'description', 'event_date']
                    ],
                    'pagination'
                ]
            ]);
    }

    public function test_can_filter_event_logs_by_type(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        EventLog::factory()->create(['tester_id' => $tester->id, 'type' => 'maintenance']);
        EventLog::factory()->create(['tester_id' => $tester->id, 'type' => 'issue']);

        $response = $this->getJson('/api/v1/event-logs?type=maintenance');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.type', 'maintenance');
    }

    public function test_can_filter_event_logs_by_tester(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester1 = $this->createTesterWithCustomer();
        $tester2 = $this->createTesterWithCustomer();

        EventLog::factory()->create(['tester_id' => $tester1->id]);
        EventLog::factory()->create(['tester_id' => $tester2->id]);

        $response = $this->getJson("/api/v1/event-logs?tester_id={$tester1->id}");

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.tester_id', $tester1->id);
    }

    public function test_can_filter_event_logs_by_date_range(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $tester = $this->createTesterWithCustomer();
        EventLog::factory()->create([
            'tester_id' => $tester->id,
            'event_date' => '2026-04-01 10:00:00'
        ]);
        EventLog::factory()->create([
            'tester_id' => $tester->id,
            'event_date' => '2026-05-01 10:00:00'
        ]);

        $response = $this->getJson('/api/v1/event-logs?start_date=2026-04-01&end_date=2026-04-30');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    // ==================== CREATE ENDPOINT TESTS ====================

    public function test_technician_can_create_event_log(): void
    {
        $technician = $this->createTechnicianUser();
        Sanctum::actingAs($technician);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
            'description' => 'Regular maintenance performed',
            'event_date' => '2026-04-02 14:30:00',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'maintenance');

        $this->assertDatabaseHas('event_logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
        ]);
    }

    public function test_guest_cannot_create_event_log(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        Sanctum::actingAs($user);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
            'description' => 'Test',
            'event_date' => '2026-04-02 14:30:00',
        ]);

        $response->assertForbidden();
    }

    public function test_create_event_log_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/event-logs', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tester_id',
                'type',
                'description',
                'event_date',
            ]);
    }

    public function test_create_event_log_validates_type(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'invalid_type',
            'description' => 'Test description',
            'event_date' => '2026-04-02 14:30:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_create_event_log_validates_description_min_length(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
            'description' => 'test', // Too short
            'event_date' => '2026-04-02 14:30:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_create_event_log_validates_event_date_format(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
            'description' => 'Valid description',
            'event_date' => '2026-04-02', // Wrong format
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_date']);
    }

    public function test_create_event_log_validates_event_date_not_future(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $tester = $this->createTesterWithCustomer();

        $response = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'maintenance',
            'description' => 'Valid description',
            'event_date' => '2026-12-31 14:30:00', // Future date
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_date']);
    }

    // ==================== SHOW ENDPOINT TESTS ====================

    public function test_can_show_event_log(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $log = $this->createTestLog();

        $response = $this->getJson("/api/v1/event-logs/{$log->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $log->id)
            ->assertJsonPath('data.type', $log->type);
    }

    public function test_show_nonexistent_event_log_returns_404(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/event-logs/9999');

        $response->assertNotFound();
    }

    // ==================== NO UPDATE/DELETE FOR EVENT LOGS ====================

    public function test_event_logs_cannot_be_updated(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $log = $this->createTestLog();

        // Event logs should only support index, store, and show
        $response = $this->putJson("/api/v1/event-logs/{$log->id}", [
            'description' => 'Updated',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_event_logs_cannot_be_deleted(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $log = $this->createTestLog();

        $response = $this->deleteJson("/api/v1/event-logs/{$log->id}");

        $response->assertMethodNotAllowed();
    }
}
