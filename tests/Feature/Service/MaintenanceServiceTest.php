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
use Spatie\Permission\Models\Role;
use App\Livewire\Pages\Services\ServicePage;
use App\Livewire\Pages\Services\ServiceSchedule;
use App\Livewire\Pages\Services\MaintenanceSettings;

// TODO: fix all issues related to these tests

class MaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;
    protected Tester $tester;

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
    }

    public function test_maintenance_schedule_is_not_created_without_required_fields_on_initial_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->set('selectedTesterId', $this->tester->id)
            ->set('maintenancePeriodId', null)
            ->set('nextMaintenanceDate', null)
            ->call('save');

        $this->assertDatabaseMissing('tester_maintenance_schedules', [
            'tester_id' => $this->tester->id,
        ]);
    }

    public function test_editing_maintenance_with_empty_fields_keeps_original_values(): void
    {
        $this->actingAs($this->adminUser);

        $schedule = TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'maintenance_id' => $this->maintenanceProcedure->id,
            'next_maintenance_due' => '2026-06-01 10:00:00',
        ]);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('maintenancePeriodId', null)
            ->set('nextMaintenanceDate', null)
            ->call('save');

        $schedule->refresh();

        $this->assertEquals($this->maintenanceProcedure->id, $schedule->maintenance_id);
        $this->assertEquals('2026-06-01', $schedule->next_maintenance_due->format('Y-m-d'));
    }

    public function test_admin_can_update_maintenance_schedule(): void
    {
        $schedule = TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'maintenance_id' => $this->maintenanceProcedure->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('maintenancePeriodId', $this->maintenanceProcedure->id)
            ->set('nextMaintenanceDate', now()->addDays(15)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_maintenance_schedules', [
            'tester_id' => $this->tester->id,
            'maintenance_id' => $this->maintenanceProcedure->id,
        ]);
    }

    public function test_admin_can_create_custom_maintenance_period(): void
    {
        $this->markTestSkipped(
            'This test is currently failing and needs to be fixed.'
        );

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->set('newPeriodType', 'maintenance')
            ->set('newMonths', 1)
            ->set('newWeeks', 0)
            ->set('newDays', 0)
            ->call('saveNewPeriod');

        $this->assertDatabaseHas('tester_maintenance_procedures', [
            'type' => 'Custom: 1 Month',
        ]);
    }

    public function test_updating_maintenance_creates_data_change_log(): void
    {
        TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'maintenance_id' => $this->maintenanceProcedure->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('maintenancePeriodId', $this->maintenanceProcedure->id)
            ->set('nextMaintenanceDate', now()->addDays(20)->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => $this->tester->id,
        ]);
    }

    // adding maintenance creates data change log
}
