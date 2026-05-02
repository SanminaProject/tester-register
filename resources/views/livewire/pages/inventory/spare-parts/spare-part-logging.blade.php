<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] mb-12 md:mb-0 rounded-[24px] md:rounded-2xl bg-white px-5 md:px-10 pt-6 md:pt-8 pb-10 md:pb-12 shadow-[0_2px_10px_rgba(0,0,0,0.02)] md:shadow-sm font-sans text-gray-800">
    <div class="flex items-center justify-between pb-6 border-b border-gray-200 flex-wrap md:flex-nowrap gap-y-4">
        <div class="flex items-center gap-4 w-full md:w-auto md:flex-1">
            <button
                type="button"
                wire:click="$dispatch('switchTab', { tab: '{{ $isEdit ? 'spare-part-details' : 'spare-parts' }}'{{ $isEdit ? ', id: ' . $sparePartId : '' }} })"
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight whitespace-nowrap">
                {{ $isEdit ? 'Edit Spare Part Details' : 'Add New Spare Part' }}
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

    <form wire:submit.prevent="save" class="pt-2 mb-[100px]">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] md:gap-x-16 pt-2">
            <div class="flex flex-col pl-0 lg:pl-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-6 lg:gap-y-8 text-left w-full max-w-4xl">
                    <!-- Required Fields -->
                    <div>
                        <x-testers.input-field label="*Name" wire:model="form.name" placeholder="" required />
                        <x-input-error :messages="$errors->get('form.name')" />
                    </div>

                    <div>
                        <x-testers.input-field label="*Quantity In Stock" type="number" wire:model="form.quantity_in_stock" placeholder="" required class="[&_input]:appearance-none [&_input]:[background-image:linear-gradient(to_bottom,#dddddd_0%,#dddddd_100%)] [&_input]:pr-8" />
                        <style>
                            input[type="number"] {
                                appearance: textfield;
                            }
                            input[type="number"]::-webkit-outer-spin-button,
                            input[type="number"]::-webkit-inner-spin-button {
                                -webkit-appearance: none;
                                appearance: none;
                                margin: 0;
                                width: 40px;
                                height: 100%;
                                cursor: pointer;
                                background-color: #dddddd;
                                border: none;
                            }
                            input[type="number"]::-webkit-outer-spin-button:hover,
                            input[type="number"]::-webkit-inner-spin-button:hover {
                                background-color: #cccccc;
                            }
                        </style>
                        <x-input-error :messages="$errors->get('form.quantity_in_stock')" />
                    </div>

                    <div>
                        <x-testers.input-field label="*Reorder Level" type="number" wire:model="form.reorder_level" placeholder="" required />
                        <x-input-error :messages="$errors->get('form.reorder_level')" />
                    </div>

                    <div>
                        <x-testers.dropdown-field label="*Tester" :options="$testers" placeholder="" wire:model="form.tester_id" required />
                        <x-input-error :messages="$errors->get('form.tester_id')" />
                    </div>

                    <!-- Optional Fields -->
                    <div>
                        <x-testers.input-field label="Manufacturer Part Number" wire:model="form.manufacturer_part_number" placeholder="" />
                        <x-input-error :messages="$errors->get('form.manufacturer_part_number')" />
                    </div>

                    <div>
                        <x-testers.textarea-field label="Description" wire:model="form.description" rows="2" placeholder="" />
                        <x-input-error :messages="$errors->get('form.description')" />
                    </div>

                    <div>
                        <x-testers.input-field label="Unit Price" type="number" step="0.01" wire:model="form.unit_price" placeholder="" />
                        <x-input-error :messages="$errors->get('form.unit_price')" />
                    </div>

                    <div>
                        <x-testers.date-field label="Last Order Date" wire:model="form.last_order_date" />
                        <x-input-error :messages="$errors->get('form.last_order_date')" />
                    </div>

                    <div>
                        <x-testers.dropdown-field label="Supplier" :options="$suppliers" placeholder="" wire:model="form.supplier_id" :manageOptions="true" :allowCreate="true" createMethod="createSupplierOption" deleteMethod="deleteSupplierOption" />
                        <x-input-error :messages="$errors->get('form.supplier_id')" />
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[15px] font-semibold text-gray-800 mb-2">Responsible Users</label>
                        <livewire:components.multiple-choice-selector
                            :selectedIds="$form->responsible_user_ids"
                            :users="$users"
                            placeholder="Select responsible user"
                        />
                        <x-input-error :messages="$errors->get('form.responsible_user_ids')" />
                    </div>
                </div>
            </div>

            <div class="hidden lg:block"></div>
        </div>
    </form>
</div>