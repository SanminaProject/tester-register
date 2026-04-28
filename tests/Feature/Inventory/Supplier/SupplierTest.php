<?php

namespace Tests\Feature\Inventory\Supplier;

use App\Livewire\Pages\Inventory\Suppliers\SupplierDetails;
use App\Livewire\Pages\Inventory\Suppliers\SupplierLogging;
use App\Livewire\Pages\Inventory\Suppliers\SuppliersTable;
use App\Models\DataChangeLog;
use App\Models\TesterSparePart;
use App\Models\TesterSparePartSupplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $normalUser;
    protected TesterSparePartSupplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();

        $this->adminUser->assignRole('Admin');

        $this->supplier = TesterSparePartSupplier::factory()->create();
    }

    public function test_suppliers_table_displays_supplier_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SuppliersTable::class)
            ->assertSee($this->supplier->supplier_name);
    }

    public function test_supplier_details_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierDetails::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->assertSet('sparePartSupplier.id', $this->supplier->id)
            ->assertSee($this->supplier->supplier_name);
    }

    public function test_admin_can_create_supplier(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class)
            ->set('form.supplier_name', 'Test Supplier')
            ->set('form.contact_person', 'John Doe')
            ->set('form.contact_email', 'john@test.com')
            ->set('form.contact_phone', '123456789')
            ->set('form.address', '123 Main Street')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_spare_part_suppliers', [
            'supplier_name' => 'Test Supplier',
        ]);
    }

    public function test_supplier_name_is_required(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class)
            ->set('form.supplier_name', '')
            ->call('save')
            ->assertHasErrors([
                'form.supplier_name' => 'required',
            ]);
    }

    public function test_nullable_supplier_fields_can_be_null(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class)
            ->set('form.supplier_name', 'Minimal Supplier')
            ->set('form.contact_person', null)
            ->set('form.contact_email', null)
            ->set('form.contact_phone', null)
            ->set('form.address', null)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_contact_email_must_be_valid_email(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class)
            ->set('form.supplier_name', 'Supplier')
            ->set('form.contact_email', 'not-an-email')
            ->call('save')
            ->assertHasErrors([
                'form.contact_email' => 'email',
            ]);
    }

    public function test_admin_can_edit_supplier(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->set('form.supplier_name', 'Updated Supplier')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tester_spare_part_suppliers', [
            'id' => $this->supplier->id,
            'supplier_name' => 'Updated Supplier',
        ]);
    }

    public function test_admin_can_delete_supplier_without_linked_spare_parts(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierDetails::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->call('deleteSupplier')
            ->assertDispatched('switchTab', tab: 'suppliers');

        $this->assertDatabaseMissing('tester_spare_part_suppliers', [
            'id' => $this->supplier->id,
        ]);
    }

    public function test_supplier_cannot_be_deleted_if_linked_to_spare_parts(): void
    {
        $this->actingAs($this->adminUser);

        TesterSparePart::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        Livewire::test(SupplierDetails::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->call('deleteSupplier')
            ->assertHasNoErrors()
            ->assertSee('cannot be deleted');

        $this->assertDatabaseHas('tester_spare_part_suppliers', [
            'id' => $this->supplier->id,
        ]);
    }

    public function test_non_admin_cannot_delete_supplier(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(SupplierDetails::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->call('deleteSupplier');

        $this->assertDatabaseHas('tester_spare_part_suppliers', [
            'id' => $this->supplier->id,
        ]);
    }

    public function test_creating_supplier_creates_data_change_log(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class)
            ->set('form.supplier_name', 'Logged Supplier')
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Added new supplier: Logged Supplier',
        ]);
    }

    public function test_updating_supplier_creates_data_change_log(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SupplierLogging::class, [
            'sparePartSupplierId' => $this->supplier->id,
        ])
            ->set('form.supplier_name', 'Updated Supplier')
            ->call('save');

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Edited supplier details:' . "\n" . '- supplier_name: [' . $this->supplier->supplier_name . '] -> [Updated Supplier]',
        ]);
    }

    public function test_deleting_supplier_creates_data_change_log(): void
    {
        $supplier = TesterSparePartSupplier::factory()->create([
            'supplier_name' => 'Supplier To Delete',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SupplierDetails::class, [
            'sparePartSupplierId' => $supplier->id,
        ])
            ->call('deleteSupplier');

        $this->assertDatabaseHas('data_change_logs', [
            'explanation' => 'Deleted supplier [ID: ' . $supplier->id . '] - Name: ' . $supplier->supplier_name,
        ]);
    }
}