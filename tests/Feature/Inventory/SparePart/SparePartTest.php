<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Pages\Inventory\SpareParts\SparePartDetails;
use App\Livewire\Pages\Inventory\SpareParts\SparePartLogging;
use App\Livewire\Pages\Inventory\SpareParts\SparePartsTable;
use App\Models\TesterSparePart;
use App\Models\TesterSparePartSupplier;
use App\Models\Tester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SparePartTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();
        $this->sparePart = TesterSparePart::factory()->create();
        $this->tester = Tester::factory()->create();
        $this->supplier = TesterSparePartSupplier::factory()->create();

        $this->adminUser->assignRole('Admin');
    }

    public function test_spare_parts_table_displays_spare_part_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartsTable::class)
            ->assertSee($this->sparePart->name)
            ->assertSee($this->sparePart->manufacturer_part_number);
    }

    public function test_spare_part_details_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartDetails::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->assertSet('sparePart.id', $this->sparePart->id)
            ->assertSee($this->sparePart->name)
            ->assertSee($this->sparePart->description);
    }

    public function test_admin_can_create_spare_part(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class)
            ->set('form.name', 'New Capacitor')
            ->set('form.manufacturer_part_number', 'CAP-123')
            ->set('form.description', 'Electrolytic capacitor')
            ->set('form.quantity_in_stock', 25)
            ->set('form.reorder_level', 5)
            ->set('form.unit_price', 4.99)
            ->set('form.tester_id', $this->tester->id)
            ->set('form.supplier_id', $this->supplier->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_spare_parts', [
            'name' => 'New Capacitor',
            'manufacturer_part_number' => 'CAP-123',
        ]);
    }

    public function test_admin_can_edit_spare_part(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->set('form.name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_spare_parts', [
            'id' => $this->sparePart->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_spare_part(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartDetails::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->call('deleteSparePart')
            ->assertDispatched('switchTab', tab: 'spare-parts');

        $this->assertDatabaseMissing('tester_spare_parts', [
            'id' => $this->sparePart->id,
        ]);
    }

    public function test_non_admin_cannot_delete_spare_part(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(SparePartDetails::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->call('deleteSparePart');

        $this->assertDatabaseHas('tester_spare_parts', [
            'id' => $this->sparePart->id,
        ]);
    }
}