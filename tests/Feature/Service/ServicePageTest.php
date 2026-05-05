<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tester;
use App\Models\TesterMaintenanceProcedure;
use App\Models\TesterCalibrationProcedure;
use App\Models\TesterMaintenanceSchedule;
use App\Models\TesterCalibrationSchedule;
use App\Models\DataChangeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use App\Livewire\Pages\Services\ServicePage;
use App\Livewire\Pages\Dashboard\EventBox;
use App\Livewire\Pages\Services\ServiceSchedule;
use App\Livewire\Pages\Services\MaintenanceSettings;
use App\Models\CalendarEvent;

// TODO: test audit logs page loads

class ServicePageTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;
    protected Tester $tester;
    protected TesterMaintenanceProcedure $maintenanceProcedure;
    protected TesterCalibrationProcedure $calibrationProcedure;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();
        $this->tester = Tester::factory()->create();
        $this->maintenanceProcedure = TesterMaintenanceProcedure::factory()->create();
        $this->calibrationProcedure = TesterCalibrationProcedure::factory()->create();

        $this->adminUser->assignRole('Admin');

        foreach (['scheduled', 'overdue', 'completed'] as $statusName) {
            DB::table('schedule_statuses')->updateOrInsert(
                ['name' => $statusName],
                ['name' => $statusName],
            );
        }
    }

    public function test_service_page_loads(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/services')
            ->assertOk()
            ->assertSee('Services');
    }

    public function test_service_page_loads_maintenance_tab(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/services?activeTab=maintenance')
            ->assertOk()
            ->assertSee('Services');
    }

    // test audit logs page loads

    public function test_selecting_tester_loads_maintenance_settings(): void
    {
        $schedule = TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'next_maintenance_due' => now()->addDays(10),
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->assertSet('selectedTesterId', $this->tester->id)
            ->assertSet('testerName', $this->tester->name);
    }

    // todo fix?
    public function test_service_schedule_loads_events(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ServiceSchedule::class)
            ->assertSet('weekOffset', 0);
    }

    public function test_can_update_schedule_status(): void
    {
        $schedule = TesterMaintenanceSchedule::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(ServiceSchedule::class)
            ->call('updateEventStatus', 'M-' . str_pad($schedule->id, 4, '0', STR_PAD_LEFT), 'completed');

        $this->assertDatabaseHas('tester_maintenance_schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_service_schedule_marks_past_due_items_as_overdue(): void
    {
        TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'maintenance_status' => DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['scheduled'])->value('id'),
            'next_maintenance_due' => now()->subDay(),
            'last_maintenance_date' => null,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ServiceSchedule::class)
            ->assertSet('upcomingEvents.0.status', 'overdue')
            ->assertSet('calendarEvents.0.event_status', 'overdue');
    }

    public function test_completing_maintenance_advances_next_date_for_setting_and_calendar(): void
    {
        DB::table('procedure_interval_units')->updateOrInsert(
            ['name' => 'months'],
            ['name' => 'months'],
        );

        $monthsUnitId = (int) DB::table('procedure_interval_units')->where('name', 'months')->value('id');

        $procedureId = (int) DB::table('tester_maintenance_procedures')->insertGetId([
            'type' => 'Quarterly maintenance',
            'interval_value' => 3,
            'description' => null,
            'interval_unit' => $monthsUnitId,
        ]);

        DB::table('event_types')->updateOrInsert(
            ['name' => 'maintenance'],
            ['name' => 'maintenance'],
        );

        $schedule = TesterMaintenanceSchedule::create([
            'tester_id' => $this->tester->id,
            'maintenance_id' => $procedureId,
            'schedule_created_date' => now(),
            'next_maintenance_due' => now()->addDay()->endOfDay(),
            'maintenance_status' => DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['scheduled'])->value('id'),
        ]);

        Sanctum::actingAs($this->adminUser);

        $completedDate = now()->toDateString();
        $expectedSettingDate = Carbon::parse($completedDate)->endOfDay()->addMonths(3)->startOfDay();
        $expectedCalendarDate = Carbon::parse($completedDate)->endOfDay()->addMonths(3);

        $this->postJson('/api/v1/maintenance-schedules/' . $schedule->id . '/complete', [
            'completed_date' => $completedDate,
            'performed_by' => $this->adminUser->name,
            'notes' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.scheduled_date', $expectedSettingDate->toDateString());

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->assertSet('nextMaintenanceDate', $expectedSettingDate->format('Y-m-d\TH:i'));

        $calendarEvent = CalendarEvent::getCalendarEvents()
            ->firstWhere('event_code', 'M-' . str_pad((string) $schedule->id, 4, '0', STR_PAD_LEFT));

        $this->assertNotNull($calendarEvent);
        $this->assertSame('scheduled', strtolower((string) $calendarEvent->event_status));
        $this->assertSame($expectedCalendarDate->toDateString(), Carbon::parse($calendarEvent->start)->toDateString());
    }

    public function test_event_box_shows_all_same_day_events(): void
    {
        $dueDate = now()->addWeek()->endOfDay();
        $scheduledStatusId = DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['scheduled'])->value('id');

        $testers = Tester::factory()->count(5)->create();

        foreach ($testers as $tester) {
            TesterMaintenanceSchedule::create([
                'tester_id' => $tester->id,
                'maintenance_id' => $this->maintenanceProcedure->id,
                'schedule_created_date' => now(),
                'next_maintenance_due' => $dueDate,
                'maintenance_status' => $scheduledStatusId,
            ]);
        }

        Livewire::test(EventBox::class, ['type' => 'events', 'title' => 'Upcoming Events'])
            ->assertSee($testers[4]->name);
    }
}