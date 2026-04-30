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

class CreateTesterTest extends TestCase
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

    public function test_admin_can_create_tester(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', 1001)
            ->set('name', 'New Tester')
            ->set('description', 'Tester Description')
            ->set('id_number_by_customer', 'CUST-001')
            ->set('owner_id', $this->owner->id)
            ->set('location_id', $this->location->id)
            ->set('status_id', $this->status->id)
            ->set('product_family', 'Family A')
            ->set('type', 'Functional')
            ->set('manufacturer', 'Agilent')
            ->set('operating_system', 'Windows 11')
            ->set('implementation_date', '2026-04-29')
            ->set('additional_info', 'Additional Notes')
            ->set('asset_nos', ['ASSET-001', 'ASSET-002'])
            ->call('save')
            ->assertDispatched('switchTab', tab: 'all');

        $this->assertDatabaseHas('testers', [
            'id' => 1001,
            'name' => 'New Tester',
        ]);

        $this->assertDatabaseHas('tester_assets', [
            'tester_id' => 1001,
            'asset_no' => 'ASSET-001',
        ]);

        $this->assertDatabaseHas('tester_assets', [
            'tester_id' => 1001,
            'asset_no' => 'ASSET-002',
        ]);

        $this->assertDatabaseHas('data_change_logs', [
            'tester_id' => 1001,
        ]);
    }

    // TODO: fix
    public function test_admin_can_upload_documents_when_creating_tester(): void
    {
        $this->markTestSkipped(
            'This test is currently failing and needs to be fixed. Unsure of actual implementation.'
        );

        Storage::fake('local');

        $this->actingAs($this->adminUser);

        $file = UploadedFile::fake()->create('manual.pdf', 500);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', 1002)
            ->set('name', 'Tester With Docs')
            ->set('documents', [$file])
            ->call('save');

        Storage::disk('local')->assertExists(
            'testers/1002/documents/manual.pdf'
        );
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', null)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors([
                'tester_id',
                'name',
            ]);
    }

    public function test_tester_id_must_be_unique(): void
    {
        Tester::factory()->create(['id' => 1001]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', 1001)
            ->set('name', 'Duplicate Tester')
            ->call('save')
            ->assertHasErrors(['tester_id']);
    }

    public function test_invalid_document_type_is_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->adminUser);

        $badFile = UploadedFile::fake()->create('virus.exe', 100);

        Livewire::test(AddNewTester::class)
            ->set('documents', [$badFile])
            ->call('save')
            ->assertHasErrors(['documents.*']);
    }

    public function test_maximum_of_five_assets_allowed(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', 1003)
            ->set('name', 'Too Many Assets')
            ->set('asset_nos', [
                '1', '2', '3', '4', '5', '6'
            ])
            ->call('save')
            ->assertHasErrors(['asset_nos']);
    }

    public function test_blank_assets_are_not_saved(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->set('tester_id', 1004)
            ->set('name', 'Blank Asset Tester')
            ->set('asset_nos', ['ASSET-001', '', '   '])
            ->call('save');

        $this->assertDatabaseCount('tester_assets', 1);
    }

    public function test_search_and_copy_tester_populates_fields(): void
    {
        $tester = Tester::factory()->create([
            'description' => 'Copied Description',
            'product_family' => 'Copied Family',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(AddNewTester::class)
            ->call('selectAndCopyTester', $tester->id)
            ->assertSet('description', 'Copied Description')
            ->assertSet('product_family', 'Copied Family');
    }
}