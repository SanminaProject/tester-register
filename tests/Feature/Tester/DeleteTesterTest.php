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

class DeleteTesterTest extends TestCase
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

    public function test_admin_can_delete_tester(): void
    {
        $tester = Tester::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester')
            ->assertDispatched('switchTab', ['tab' => 'all']);

        $this->assertDatabaseMissing('testers', [
            'id' => $tester->id,
        ]);
    }

    public function test_non_admin_cannot_delete_tester(): void
    {
        $tester = Tester::factory()->create();

        $this->actingAs($this->normalUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester')
            ->assertForbidden();
    }

    public function test_cannot_delete_tester_with_bound_fixtures(): void
    {
        $tester = Tester::factory()->create();

        Fixture::factory()->create([
            'tester_id' => $tester->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester');

        $this->assertDatabaseHas('testers', [
            'id' => $tester->id,
        ]);
    }

    public function test_deleting_tester_removes_assets(): void
    {
        $tester = Tester::factory()->create();

        TesterAsset::factory()->create([
            'tester_id' => $tester->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester');

        $this->assertDatabaseMissing('tester_assets', [
            'tester_id' => $tester->id,
        ]);
    }

    public function test_deleting_tester_removes_documents_folder(): void
    {
        Storage::fake('local');

        $tester = Tester::factory()->create();

        Storage::disk('local')->put(
            "testers/{$tester->id}/documents/file.pdf",
            'content'
        );

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester');

        Storage::disk('local')->assertMissing(
            "testers/{$tester->id}/documents/file.pdf"
        );
    }

    public function test_deleting_tester_creates_audit_log(): void
    {
        $tester = Tester::factory()->create([
            'name' => 'Delete Me',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(TesterDetails::class, ['testerId' => $tester->id])
            ->call('deleteTester');

        $log = DataChangeLog::where('tester_id', null)->latest('id')->first();

        $this->assertStringContainsString(
            "Deleted tester details (ID: {$tester->id}):",
            $log->explanation
        );

        $this->assertStringContainsString(
            "- name: [Delete Me]",
            $log->explanation
        );
    }
}