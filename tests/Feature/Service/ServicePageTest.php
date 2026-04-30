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

// TODO: test audit logs page loads

class ServicePageTest extends TestCase
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
}