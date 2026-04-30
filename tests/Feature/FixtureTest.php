<?php

namespace Tests\Feature;

use App\Livewire\Pages\Fixtures\FixturePage;
use App\Livewire\Pages\Fixtures\FixtureTable;
use App\Livewire\Pages\Fixtures\FixtureLogging;
use App\Livewire\Pages\Fixtures\FixtureDetails;
use App\Livewire\Components\DataTable;
use App\Models\AssetStatus;
use App\Models\DataChangeLog;
use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterAndFixtureLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FixtureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;

    protected Tester $tester;
    protected TesterAndFixtureLocation $location;
    protected AssetStatus $status;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole($this->adminRole);

        $this->tester = Tester::factory()->create();
        $this->location = TesterAndFixtureLocation::factory()->create();
        $this->status = AssetStatus::factory()->create();
        $this->fixture = Fixture::factory()->create([
            'tester_id' => $this->tester->id,
            'location_id' => $this->location->id,
            'fixture_status' => $this->status->id,
        ]);
    }

    public function test_fixture_page_loads(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/fixtures')
            ->assertOk()
            ->assertSeeLivewire(FixturePage::class);
    }

    public function test_fixture_table_is_rendered_by_default(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixturePage::class)
            ->assertSet('activeTab', 'all')
            ->assertSeeLivewire(FixtureTable::class);
    }

    public function test_fixture_table_displays_fixture_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureTable::class)
            ->assertSee('Fixture List')
            ->assertSee($this->fixture->name);
    }

    public function test_fixture_audit_logs_table_displays_correct_log_data(): void
    {
        $this->actingAs($this->adminUser);

        $log = DataChangeLog::factory()->create([
            'fixture_id'   => $this->fixture->id,
            'user_id'      => $this->adminUser->id,
            'changed_at'   => now(),
            'explanation'  => 'Edited fixture details',
        ]);

        Livewire::test(DataTable::class, [
            'type' => 'fixture-audit-logs',
            'title' => 'Fixture Audit Logs',
            'headers' => [
                'id' => 'ID',
                'changed_at' => 'Changed At',
                'fixture.id' => 'Fixture ID',
                'fixture.name' => 'Fixture Name',
                'explanation' => 'Action Details',
                'user.name' => 'User',
                'user.email' => 'Email',
            ],
        ])
            ->assertSee((string) $log->id)
            ->assertSee('Edited fixture details')
            ->assertSee((string) $this->fixture->id)
            ->assertSee($this->fixture->name)
            ->assertSee($this->adminUser->email);
    }

    public function test_fixture_details_loads_fixture(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureDetails::class, ['fixtureId' => $this->fixture->id])
            ->assertSet('fixture.id', $this->fixture->id)
            ->assertSee($this->fixture->name);
    }

    public function test_admin_can_create_fixture(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureLogging::class)
            ->set('form.name', 'New Fixture')
            ->set('form.description', 'Test Desc')
            ->set('form.manufacturer', 'Agilent')
            ->set('form.tester_id', $this->tester->id)
            ->set('form.location_id', $this->location->id)
            ->set('form.fixture_status', $this->status->id)
            ->call('save')
            ->assertDispatched('saved')
            ->assertDispatched('switchTab', tab: 'all');

        $this->assertDatabaseHas('fixtures', [
            'name' => 'New Fixture',
        ]);

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Added new fixture: New Fixture',
        ]);
    }

    public function test_admin_can_update_fixture(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureLogging::class, ['fixtureId' => $this->fixture->id])
            ->set('form.name', 'Updated Fixture')
            ->call('save')
            ->assertDispatched('saved')
            ->assertDispatched('switchTab', tab: 'all');

        $this->assertDatabaseHas('fixtures', [
            'id' => $this->fixture->id,
            'name' => 'Updated Fixture',
        ]);

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Edited fixture details:' . "\n" . '- name: [' . $this->fixture->name . '] -> [Updated Fixture]',
        ]);
    }

    public function test_admin_can_delete_fixture(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureDetails::class, ['fixtureId' => $this->fixture->id])
            ->call('deleteFixture')
            ->assertDispatched('switchTab', tab: 'all');

        $this->assertDatabaseMissing('fixtures', [
            'id' => $this->fixture->id,
        ]);

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Deleted fixture [ID: ' . $this->fixture->id . '] - Name: ' . $this->fixture->name,
        ]);
    }

    public function test_non_admin_cannot_delete_fixture(): void
    {
        $fixture = Fixture::factory()->create([
            'tester_id' => $this->tester->id,
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(FixtureDetails::class, ['fixtureId' => $fixture->id])
            ->call('deleteFixture')
            ->assertForbidden();
    }

    public function test_fixture_requires_name_and_tester(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FixtureLogging::class)
            ->set('form.name', '')
            ->set('form.tester_id', null)
            ->call('save')
            ->assertHasErrors([
                'form.name',
                'form.tester_id',
            ]);
    }

    public function test_fixture_page_switches_to_details_tab(): void
    {
        Livewire::test(FixturePage::class)
            ->call('switchTab', 'details', $this->fixture->id)
            ->assertSet('activeTab', 'details')
            ->assertSet('selectedFixtureId', $this->fixture->id);
    }

    public function test_fixture_page_switches_to_edit_tab(): void
    {
        Livewire::test(FixturePage::class)
            ->call('switchTab', 'edit', 10)
            ->assertSet('activeTab', 'edit')
            ->assertSet('selectedFixtureId', 10);
    }
}