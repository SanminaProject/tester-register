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
use App\Models\ProcedureIntervalUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use App\Livewire\Pages\Services\ServicePage;
use App\Livewire\Pages\Services\ServiceSchedule;
use App\Livewire\Pages\Services\MaintenanceSettings;

class CalibrationServiceTest extends TestCase
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

    public function test_calibration_schedule_is_not_created_without_required_fields_on_initial_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->set('selectedTesterId', $this->tester->id)
            ->set('calibrationPeriodId', null)
            ->set('nextCalibrationDate', null)
            ->call('save');

        $this->assertDatabaseMissing('tester_calibration_schedules', [
            'tester_id' => $this->tester->id,
        ]);
    }

    public function test_editing_calibration_with_empty_fields_keeps_original_values(): void
    {
        $this->actingAs($this->adminUser);

        $schedule = TesterCalibrationSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'calibration_id' => $this->calibrationProcedure->id,
            'next_calibration_due' => '2026-07-01 10:00:00',
        ]);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('calibrationPeriodId', null)
            ->set('nextCalibrationDate', null)
            ->call('save');

        $schedule->refresh();

        $this->assertEquals($this->calibrationProcedure->id, $schedule->calibration_id);
        $this->assertEquals('2026-07-01', $schedule->next_calibration_due->format('Y-m-d'));
    }

    public function test_admin_can_update_calibration_schedule(): void
    {
        TesterCalibrationSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'calibration_id' => $this->calibrationProcedure->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('calibrationPeriodId', $this->calibrationProcedure->id)
            ->set('nextCalibrationDate', now()->addDays(15)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_calibration_schedules', [
            'tester_id' => $this->tester->id,
            'calibration_id' => $this->calibrationProcedure->id,
        ]);
    }

    public function test_admin_can_create_custom_calibration_period(): void
    {
        $daysUnit = ProcedureIntervalUnit::firstOrCreate([
            'name' => 'Days',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->set('newPeriodType', 'calibration')
            ->set('newMonths', 8)
            ->set('newWeeks', 3)
            ->set('newDays', 0)
            ->call('saveNewPeriod');

        $this->assertDatabaseHas('tester_calibration_procedures', [
            'type' => 'Custom: 8 Months 3 Weeks',
            'interval_value' => 261,
        ]);
    }

    public function test_updating_calibration_creates_data_change_log(): void
    {
        TesterCalibrationSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'calibration_id' => $this->calibrationProcedure->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('calibrationPeriodId', $this->calibrationProcedure->id)
            ->set('nextCalibrationDate', now()->addDays(20)->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => $this->tester->id,
        ]);
    }
}
