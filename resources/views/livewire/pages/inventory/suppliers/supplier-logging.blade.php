<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] mb-12 md:mb-0 rounded-[24px] md:rounded-2xl bg-white px-5 md:px-10 pt-6 md:pt-8 pb-10 md:pb-12 shadow-[0_2px_10px_rgba(0,0,0,0.02)] md:shadow-sm font-sans text-gray-800">
    <div class="flex items-center justify-between pb-6 border-b border-gray-200 flex-wrap md:flex-nowrap gap-y-4">
        <div class="flex items-center gap-4 w-full md:w-auto md:flex-1">
            <button
                type="button"
                wire:click="$dispatch('switchTab', { tab: '{{ $isEdit ? 'supplier-details' : 'suppliers' }}'{{ $isEdit ? ', id: ' . $sparePartSupplierId : '' }} })"
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight whitespace-nowrap">
                {{ $isEdit ? 'Edit Supplier' : 'Add New Supplier' }}
            </h2>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto md:flex-1 justify-end order-2 md:order-none">
            <x-action-message on="saved" class="me-3">
                Saved.
            </x-action-message>

            <button
                class="bg-primary hover:bg-[#8A0028] text-white text-[15px] font-medium px-8 py-2 md:py-2.5 rounded-full transition-colors flex-shrink-0"
                wire:click="save"
                type="button">
                Save
            </button>
        </div>
    </div>

    <form wire:submit.prevent="save" class="pt-2">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] md:gap-x-16 pt-2">
            <div class="flex flex-col pl-0 lg:pl-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-6 lg:gap-y-8 text-left w-full max-w-4xl">
                    <!-- Required Fields -->
                    <div class="lg:col-span-2">
                        <x-testers.input-field label="*Supplier Name" wire:model="form.supplier_name" placeholder="" required />
                        <x-input-error :messages="$errors->get('form.supplier_name')" />
                    </div>

                    <!-- Optional Fields -->
                    <div>
                        <x-testers.input-field label="Contact Person" wire:model="form.contact_person" placeholder="" />
                        <x-input-error :messages="$errors->get('form.contact_person')" />
                    </div>

                    <div>
                        <x-testers.input-field label="Email" wire:model="form.contact_email" placeholder="" />
                        <x-input-error :messages="$errors->get('form.contact_email')" />
                    </div>

                    <div>
                        <x-testers.input-field label="Phone" wire:model="form.contact_phone" placeholder="" />
                        <x-input-error :messages="$errors->get('form.contact_phone')" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-testers.textarea-field label="Address" wire:model="form.address" rows="2" placeholder="" />
                        <x-input-error :messages="$errors->get('form.address')" />
                    </div>
                </div>
            </div>

            <div class="hidden lg:block"></div>
        </div>
    </form>
</div>