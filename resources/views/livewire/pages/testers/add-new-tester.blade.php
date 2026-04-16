<div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-1 border-b pb-4">
        <h3 class="text-xl font-bold text-[#2C3E50]">Add New Tester</h3>
        <button
            class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition text-sm"
            type="button">
            Edit Tester
        </button>

        <button
            class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition text-sm"
            wire:click="$dispatch('switchTab', { tab: 'all' })"
            type="button">
            Back to List
        </button>
    </div>

    <div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-1">
            <div class="space-y-6 text-left">
                <div class="space-y-4">
                    <x-testers.input-field
                        label="ID"
                        wire:model="tester_id"
                        placeholder="Enter Tester ID..." />
                    <x-testers.input-field
                        label="Name"
                        wire:model="name"
                        placeholder="Enter Tester Name..." />
                    <x-testers.textarea-field
                        label="Description"
                        wire:model="description"
                        rows="3" />

                    <div>
                        <x-testers.input-field
                            label="Customer ID"
                            wire:model="id_number_by_customer"
                            placeholder="Enter Customer ID..." />
                    </div>
                    <div>
                        <x-testers.dropdown-field
                            label="Owner"
                            :options="$owners"
                            placeholder="Select Owner..."
                            wire:model="owner_id">
                            <option value="new" class="text-blue-600 font-bold">+ Add New Owner...</option>
                            </x-dropdown-field>
                    </div>
                    <div>
                        <x-testers.date-field
                            label="Implementation Date"
                            wire:model="implementation_date" />
                    </div>
                </div>
            </div>

            <div class="space-y-6 text-left">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-100 relative">
                    <label class="block text-sm font-medium text-blue-800 mb-2">Search to Copy</label>
                    <input type="text" wire:model.live.debounce.300ms="search_query" placeholder="Search by name or ID..." class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @if(!empty($search_results))
                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-auto">
                        @foreach($search_results as $result)
                        <div class="px-4 py-2 hover:bg-gray-50 flex justify-between items-center border-b">
                            <span class="text-sm text-gray-700">{{ $result['name'] }}</span>
                            <button type="button" wire:click="selectAndCopyTester({{ $result['id'] }})" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">Copy</button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-testers.dropdown-field
                            label="Prod Family"
                            :options="$families"
                            placeholder="Select Family..."
                            wire:model="product_family">
                            <option value="new" class="text-blue-600 font-bold">+ Add New Family...</option>
                            </x-dropdown-field>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-testers.dropdown-field
                            label="Manufacturer"
                            :options="$manufacturers"
                            placeholder="Select Manufacturer..."
                            wire:model="manufacturer">
                            <option value="new" class="text-blue-600 font-bold">+ Add New Manufacturer...</option>
                            </x-dropdown-field>
                    </div>
                    <div>
                        <x-testers.dropdown-field
                            label="OS"
                            :options="$os_versions"
                            placeholder="Select OS..."
                            wire:model="operating_system">
                            <option value="new" class="text-blue-600 font-bold">+ Add New OS...</option>
                            </x-dropdown-field>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-4">
                    <div>
                        <x-testers.dropdown-field
                            label="Location"
                            :options="$locations"
                            placeholder="Select Location..."
                            wire:model="location_id">
                            <option value="new" class="text-blue-600 font-bold">+ Add New Location...</option>
                            </x-dropdown-field>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-testers.dropdown-field
                                label="Status"
                                :options="$statuses"
                                placeholder=""
                                wire:model="status_id" />
                        </div>
                        <div>
                            <x-testers.input-field
                                label="Type"
                                wire:model="type" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Documents (Images/PDF)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition cursor-pointer relative">
                            <input type="file" wire:model="documents" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="text-sm text-gray-600">Click or Drag to upload</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>