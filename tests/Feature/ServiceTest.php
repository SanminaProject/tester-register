<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tester;
use App\Models\TesterMaintenanceSchedule;
use App\Models\DataChangeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use App\Livewire\Pages\Services\ServicePage;
use App\Livewire\Pages\Services\ServiceSchedule;

class ServiceTest extends TestCase
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

        $this->adminUser->assignRole('Admin');
    }

    public function test_service_page_loads(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/services')
            ->assertOk()
            ->assertSeeLivewire(ServicePage::class);
    }

    public function test_service_page_loads_maintenance_tab(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/services?activeTab=maintenance')
            ->assertOk()
            ->assertSeeLivewire(ServicePage::class);
    }

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

    public function test_service_schedule_loads_events(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ServiceSchedule::class)
            ->assertSet('weekOffset', 0);
    }

    public function test_maintenance_form_requires_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->set('selectedTesterId', $this->tester->id)
            ->set('maintenancePeriodId', null)
            ->set('nextMaintenanceDate', null)
            ->call('save')
            ->assertHasErrors([
                'maintenancePeriodId' => 'required',
                'nextMaintenanceDate' => 'required',
            ]);
    }

    public function test_admin_can_update_maintenance_schedule(): void
    {
        $schedule = TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('maintenancePeriodId', 1)
            ->set('nextMaintenanceDate', now()->addDays(15)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_maintenance_schedules', [
            'tester_id' => $this->tester->id,
            'maintenance_id' => 1,
        ]);
    }

     public function test_non_admin_cannot_update_maintenance_schedule(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('maintenancePeriodId', 2)
            ->call('save');

        $this->assertDatabaseMissing('data_change_logs', [
            'tester_id' => $this->tester->id,
        ]);
    }

    public function test_admin_can_create_custom_maintenance_period(): void
    {
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

    public function test_updating_maintenance_creates_data_change_log(): void
    {
        TesterMaintenanceSchedule::factory()->create([
            'tester_id' => $this->tester->id,
            'maintenance_id' => 1,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(MaintenanceSettings::class)
            ->call('selectTester', $this->tester->id)
            ->set('isEditing', true)
            ->set('maintenancePeriodId', 2)
            ->set('nextMaintenanceDate', now()->addDays(20)->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => $this->tester->id,
        ]);
    }

    //adding maintenance creates data change log
    // same for calibrations
}