<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">
                    {{ $isEdit ? 'Edit Supplier' : 'Add New Supplier' }}
                </h3>

                <x-action-message on="saved" class="me-3">
                    Saved.
                </x-action-message>

                <x-primary-button type="submit">
                    Save
                </x-primary-button>
            </div>

            <div class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <x-input-label value="Supplier Name" />
                        <x-text-input wire:model="form.supplier_name" />
                        <x-input-error :messages="$errors->get('form.supplier_name')" />
                    </div>

                    <div>
                        <x-input-label value="Contact Person" />
                        <x-text-input wire:model="form.contact_person" />
                        <x-input-error :messages="$errors->get('form.contact_person')" />
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <x-input-label value="Email" />
                        <x-text-input wire:model="form.contact_email" />
                        <x-input-error :messages="$errors->get('form.contact_email')" />
                    </div>

                    <div>
                        <x-input-label value="Phone" />
                        <x-text-input wire:model="form.contact_phone" />
                        <x-input-error :messages="$errors->get('form.contact_phone')" />
                    </div>

                </div>

                <div>
                    <x-input-label value="Address" />
                    <x-text-input wire:model="form.address" />
                    <x-input-error :messages="$errors->get('form.address')" />
                </div>

            </div>
        </form>
    </div>
</div>