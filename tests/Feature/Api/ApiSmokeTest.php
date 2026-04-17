<?php

namespace Tests\Feature\Api;

use App\Models\TesterMaintenanceSchedule as MaintenanceSchedule;
use App\Models\Tester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token(): void
    {
        Role::findOrCreate('Guest', 'web');

        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'API',
            'last_name' => 'User',
            'phone' => '+358401234567',
            'email' => 'api-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user',
                    'roles',
                ],
                'code',
            ]);
    }

    public function test_protected_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/customers');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 401)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    public function test_admin_can_crud_customer(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin', 'web');
        $admin->assignRole('Admin');

        Sanctum::actingAs($admin);

        $createResponse = $this->postJson('/api/v1/customers', [
            'name' => 'Acme Labs',
        ]);

        $customerId = $createResponse->json('data.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 201);

        $this->getJson('/api/v1/customers/'.$customerId)
            ->assertOk()
            ->assertJsonPath('data.name', 'Acme Labs');

        $this->patchJson('/api/v1/customers/'.$customerId, [
            'name' => 'Acme Labs Updated',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Acme Labs Updated');

        $this->deleteJson('/api/v1/customers/'.$customerId)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_guest_cannot_create_customer(): void
    {
        $guest = User::factory()->create();
        Role::findOrCreate('Guest', 'web');
        $guest->assignRole('Guest');

        Sanctum::actingAs($guest);

        $this->postJson('/api/v1/customers', [
            'name' => 'Blocked Customer',
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 403);
    }

    public function test_complete_maintenance_creates_event_log(): void
    {
        $manager = User::factory()->create();
        Role::findOrCreate('Manager', 'web');
        $manager->assignRole('Manager');

        Sanctum::actingAs($manager);

        $customerId = (int) DB::table('tester_customers')->insertGetId([
            'name' => 'Nokia Labs',
        ]);

        $intervalUnitId = (int) DB::table('procedure_interval_units')->insertGetId([
            'name' => 'months',
        ]);

        $maintenanceProcedureId = (int) DB::table('tester_maintenance_procedures')->insertGetId([
            'type' => 'Routine maintenance',
            'interval_value' => 6,
            'description' => null,
            'interval_unit' => $intervalUnitId,
        ]);

        $eventTypeId = (int) DB::table('event_types')->insertGetId([
            'name' => 'maintenance',
        ]);

        $tester = Tester::create([
            'owner_id' => $customerId,
            'name' => 'AX-500',
            'id_number_by_customer' => 'TS-5000',
        ]);

        $schedule = MaintenanceSchedule::create([
            'tester_id' => $tester->id,
            'maintenance_id' => $maintenanceProcedureId,
            'schedule_created_date' => now(),
            'next_maintenance_due' => now()->addDay(),
        ]);

        $this->postJson('/api/v1/maintenance-schedules/'.$schedule->id.'/complete', [
            'completed_date' => now()->toDateString(),
            'performed_by' => 'Tech User',
            'notes' => 'Completed successfully',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tester_event_logs', [
            'tester_id' => $tester->id,
            'event_type' => $eventTypeId,
            'maintenance_schedule_id' => $schedule->id,
            'created_by_user_id' => $manager->id,
        ]);
    }
}
