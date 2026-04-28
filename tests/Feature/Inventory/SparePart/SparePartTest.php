<?php

namespace Tests\Feature\Inventory\SparePart;

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

    public function test_spare_part_form_validates_required_fields_with_min(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class)
            ->set('form.name', 'Name')
            ->set('form.quantity_in_stock', -1)
            ->set('form.reorder_level', -1)
            ->set('form.unit_price', -10)
            ->set('form.tester_id', $this->tester->id)
            ->call('save')
            ->assertHasErrors([
                'form.quantity_in_stock' => 'min',
                'form.reorder_level' => 'min',
                'form.unit_price' => 'min',
            ]);
    }

    // test about what fields are required and checking that they are required?

    public function test_spare_part_form_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class)
            ->set('form.name', '')
            ->set('form.quantity_in_stock', null)
            ->set('form.reorder_level', null)
            ->set('form.tester_id', null)
            ->call('save')
            ->assertHasErrors([
                'form.name' => 'required',
                'form.quantity_in_stock' => 'required',
                'form.reorder_level' => 'required',
                'form.tester_id' => 'required',
            ]);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class)
            ->set('form.name', 'Test Part')
            ->set('form.quantity_in_stock', 1)
            ->set('form.reorder_level', 1)
            ->set('form.tester_id', $this->tester->id)
            ->set('form.manufacturer_part_number', null)
            ->set('form.last_order_date', null)
            ->set('form.unit_price', null)
            ->set('form.description', null)
            ->set('form.supplier_id', null)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_admin_can_edit_spare_part(): void
    {
        $sparePart = TesterSparePart::factory()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->set('form.name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_spare_parts', [
            'id' => $sparePart->id,
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
        $sparePart = TesterSparePart::factory()->create();

        Livewire::test(SparePartDetails::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->call('deleteSparePart');

        $this->assertDatabaseHas('tester_spare_parts', [
            'id' => $sparePart->id,
        ]);
    }

    public function test_creating_spare_part_creates_data_change_log(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class)
            ->set('form.name', 'Logged Spare Part')
            ->set('form.quantity_in_stock', 10)
            ->set('form.reorder_level', 2)
            ->set('form.tester_id', $this->tester->id)
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Added new spare part: Logged Spare Part',
        ]);
    }

    public function test_updating_spare_part_creates_data_change_log(): void
    {
        $sparePart = TesterSparePart::factory()->create([
            'name' => 'Original Part',
            'quantity_in_stock' => 5,
            'reorder_level' => 1,
            'tester_id' => $this->tester->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SparePartLogging::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->set('form.name', 'Updated Part')
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'spare_part_id' => $sparePart->id,
            'explanation' => 'Edited spare part details:' . "\n" . '- name: [Original Part] -> [Updated Part]',
        ]);

        $this->assertDatabaseCount('data_change_logs', 1);
    }

    public function test_deleting_spare_part_creates_data_change_log(): void
    {
        $sparePart = TesterSparePart::factory()->create([
            'name' => 'Delete Me',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SparePartDetails::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->call('deleteSparePart');

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Deleted spare part [ID: ' . $sparePart->id . '] - Name: Delete Me',
        ]);
    }
}