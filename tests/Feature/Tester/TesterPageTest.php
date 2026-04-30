<?php

namespace Tests\Feature\Tester;

use App\Livewire\Pages\Testers\TesterPage;
use App\Livewire\Pages\Testers\AllTesters;
use App\Livewire\Pages\Testers\AddNewTester;
use App\Livewire\Pages\Testers\TesterDetails;
use App\Livewire\Components\DataTable;
use App\Models\AssetStatus;
use App\Models\DataChangeLog;
use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterAsset;
use App\Models\TesterCustomer;
use App\Models\TesterAndFixtureLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TesterPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;

    protected TesterCustomer $owner;
    protected TesterAndFixtureLocation $location;
    protected AssetStatus $status;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole($adminRole);

        $this->owner = TesterCustomer::factory()->create();
        $this->location = TesterAndFixtureLocation::factory()->create();
        $this->status = AssetStatus::factory()->create();
        $this->tester = Tester::factory()->create();
    }

    public function test_tester_page_loads(): void
    {
        $this->actingAs($this->adminUser);

        $this->get('/testers')
            ->assertOk()
            ->assertSeeLivewire(TesterPage::class);
    }

     public function test_tester_table_is_rendered_by_default(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(TesterPage::class)
            ->assertSet('activeTab', 'all')
            ->assertSeeLivewire(AllTesters::class);
    }

    public function test_tester_page_switches_to_details_tab(): void
    {
        Livewire::test(TesterPage::class)
            ->call('switchTab', 'details', $this->tester->id)
            ->assertSet('activeTab', 'details')
            ->assertSet('selectedTesterId', $this->tester->id);
    }

    public function test_tester_page_switches_to_edit_tab(): void
    {
        Livewire::test(TesterPage::class)
            ->call('switchTab', 'edit', $this->tester->id)
            ->assertSet('activeTab', 'edit')
            ->assertSet('selectedTesterId', $this->tester->id);
    }

    public function test_tester_details_loads_correct_tester(): void
    {
        $tester = Tester::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->assertSet('tester.id', $tester->id)
            ->assertSee($tester->name);
    }

    public function test_tester_audit_logs_display_correctly(): void
    {
        $tester = Tester::factory()->create();

        $log = DataChangeLog::factory()->create([
            'tester_id' => $tester->id,
            'user_id' => $this->adminUser->id,
            'explanation' => 'Edited tester details',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(DataTable::class, [
            'type' => 'tester-audit-logs',
            'title' => 'Tester Audit Logs',
            'headers' => [
                'id' => 'ID',
                'changed_at' => 'Changed At',
                'tester.id' => 'Tester ID',
                'tester.name' => 'Tester Name',
                'explanation' => 'Action Details',
                'user.name' => 'User',
            ],
        ])
            ->assertSee((string) $log->id)
            ->assertSee('Edited tester details')
            ->assertSee((string) $tester->id)
            ->assertSee($tester->name);
    }
}