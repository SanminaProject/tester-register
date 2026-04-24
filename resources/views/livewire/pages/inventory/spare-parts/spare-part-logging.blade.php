<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">{{ $isEdit ? 'Edit Spare Part Details' : 'Add New Spare Part' }}</h3>

                <x-action-message on="saved" class="me-3">
                    Saved.
                </x-action-message>

                <x-primary-button type="submit">
                    Save
                </x-primary-button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input label="Name" wire:model="form.name"/>
                        <x-input-error :messages="$errors->get('form.name')" />
                    </div>

                    <div>
                        <x-input-label for="manufacturer_part_number" value="Manufacturer Part Number" />
                        <x-text-input label="Manufacturer Part Number" wire:model="form.manufacturer_part_number"/>
                        <x-input-error :messages="$errors->get('form.manufacturer_part_number')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <x-text-input label="Description" wire:model="form.description" />
                        <x-input-error :messages="$errors->get('form.description')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Quantity In Stock" />
                        <x-text-input type="number" wire:model="form.quantity_in_stock" />
                        <x-input-error :messages="$errors->get('form.quantity_in_stock')" />
                    </div>

                    <div>
                        <x-input-label value="Reorder Level" />
                        <x-text-input type="number" wire:model="form.reorder_level" />
                        <x-input-error :messages="$errors->get('form.reorder_level')" />
                    </div>

                    <div>
                        <x-input-label value="Unit Price" />
                        <x-text-input type="number" step="0.01" wire:model="form.unit_price" />
                        <x-input-error :messages="$errors->get('form.unit_price')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Last Order Date" />
                        <x-testers.date-field wire:model="form.last_order_date" />
                        <x-input-error :messages="$errors->get('form.last_order_date')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-select-field
                            label="Tester"
                            wire:model="form.tester_id"
                            :options="$testers"
                        />
                        <x-input-error :messages="$errors->get('form.tester_id')" />
                    </div>

                    <div>
                        <x-select-field
                            label="Supplier"
                            wire:model="form.supplier_id"
                            :options="$suppliers"
                        />
                        <x-input-error :messages="$errors->get('form.supplier_id')" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>