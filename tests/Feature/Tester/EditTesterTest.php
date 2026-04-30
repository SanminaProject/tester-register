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

class EditTesterTest extends TestCase
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

    public function test_admin_can_edit_tester(): void
    {
        $tester = Tester::factory()->create([
            'name' => 'Old Tester Name',
            'description' => 'Old Description',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class, ['testerId' => $tester->id])
            ->set('name', 'Updated Tester Name')
            ->set('description', 'Updated Description')
            ->call('save')
            ->assertDispatched('switchTab', ['tab' => 'details', 'id' => $tester->id]);

        $this->assertDatabaseHas('testers', [
            'id' => $tester->id,
            'name' => 'Updated Tester Name',
            'description' => 'Updated Description',
        ]);

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => $tester->id,
        ]);
    }

    public function test_admin_can_change_tester_id(): void
    {
        $tester = Tester::factory()->create(['id' => 100]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class, ['testerId' => 100])
            ->set('tester_id', 200)
            ->call('save');

        $this->assertDatabaseMissing('testers', ['id' => 100]);

        $this->assertDatabaseHas('testers', ['id' => 200]);
    }

    public function test_documents_move_when_tester_id_changes(): void
    {
        Storage::fake('local');

        $tester = Tester::factory()->create(['id' => 100]);

        Storage::disk('local')->put(
            'testers/100/documents/manual.pdf',
            'dummy content'
        );

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class, ['testerId' => 100])
            ->set('tester_id', 200)
            ->call('save');

        Storage::disk('local')->assertMissing(
            'testers/100/documents/manual.pdf'
        );

        Storage::disk('local')->assertExists(
            'testers/200/documents/manual.pdf'
        );
    }

    public function test_editing_tester_replaces_assets_correctly(): void
    {
        $tester = Tester::factory()->create();

        TesterAsset::factory()->create([
            'tester_id' => $tester->id,
            'asset_no' => 'OLD-ASSET',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class, ['testerId' => $tester->id])
            ->set('asset_nos', ['NEW-ASSET-1', 'NEW-ASSET-2'])
            ->call('save');

        $this->assertDatabaseMissing('tester_assets', [
            'tester_id' => $tester->id,
            'asset_no' => 'OLD-ASSET',
        ]);

        $this->assertDatabaseHas('tester_assets', [
            'tester_id' => $tester->id,
            'asset_no' => 'NEW-ASSET-1',
        ]);

        $this->assertDatabaseHas('tester_assets', [
            'tester_id' => $tester->id,
            'asset_no' => 'NEW-ASSET-2',
        ]);
    }

    public function test_editing_tester_creates_audit_log(): void
    {
        $tester = Tester::factory()->create([
            'name' => 'Original Name',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class, ['testerId' => $tester->id])
            ->set('name', 'Changed Name')
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => $tester->id,
        ]);

        $log = DataChangeLog::where('tester_id', $tester->id)->latest('id')->first();

        $this->assertStringContainsString(
            'Edited tester details:',
            $log->explanation
        );

        $this->assertStringContainsString(
            "- name: [Original Name] -> [Changed Name]",
            $log->explanation
        );
    }
}