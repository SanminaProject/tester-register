<?php

namespace Tests\Feature\Api;

use App\Models\Tester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IssueEventLogHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_create_update_delete_writes_history_rows(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin', 'web');
        $admin->assignRole('Admin');

        Sanctum::actingAs($admin);

        $eventTypeId = (int) DB::table('event_types')->insertGetId([
            'name' => 'issue',
        ]);

        $statusId = (int) DB::table('issue_statuses')->insertGetId([
            'name' => 'Active',
        ]);

        $tester = Tester::create([
            'name' => 'Issue Tester',
            'id_number_by_customer' => 'I-100',
        ]);

        $createResponse = $this->postJson('/api/v1/event-logs', [
            'tester_id' => $tester->id,
            'type' => 'issue',
            'event_date' => now()->toDateString(),
            'description' => 'Issue description text',
            'metadata' => [
                'resolution_description' => 'Initial solution text',
                'issue_status' => $statusId,
            ],
        ]);

        $issueId = (int) $createResponse->json('data.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tester_event_logs', [
            'id' => $issueId,
            'tester_id' => $tester->id,
            'event_type' => $eventTypeId,
        ]);

        $this->assertDatabaseHas('tester_event_logs', [
            'tester_id' => $tester->id,
            'event_type' => $eventTypeId,
            'description' => '[HISTORY] Created issue #' . $issueId,
        ]);

        $this->patchJson('/api/v1/event-logs/' . $issueId, [
            'tester_id' => $tester->id,
            'type' => 'issue',
            'event_date' => now()->toDateString(),
            'description' => 'Issue description updated',
            'metadata' => [
                'resolution_description' => 'Updated solution text',
            ],
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tester_event_logs', [
            'tester_id' => $tester->id,
            'event_type' => $eventTypeId,
            'description' => '[HISTORY] Updated issue #' . $issueId . ' | fields: description, resolution_description',
        ]);

        $this->deleteJson('/api/v1/event-logs/' . $issueId)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tester_event_logs', [
            'tester_id' => $tester->id,
            'event_type' => $eventTypeId,
            'description' => '[HISTORY] Deleted issue #' . $issueId,
        ]);
    }
}
