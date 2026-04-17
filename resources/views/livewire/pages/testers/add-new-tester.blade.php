<div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-1 border-b pb-4">
        <h3 class="text-xl font-bold text-[#2C3E50]">Add New Tester</h3>
        <button
            class="px-4 py-2 rounded-full bg-primary text-white font-semibold hover:bg-secondary transition text-sm"
            type="button">
            Edit Tester
        </button>

        <button
            class="px-4 py-2 rounded-full bg-primary text-white font-semibold hover:bg-secondary transition text-sm"
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
                        rows="1" />

                    <div>
                        <x-testers.input-field
                            label="Customer ID"
                            wire:model="id_number_by_customer"
                            placeholder="Enter Customer ID..." />
                    </div>
                    <div>
                        <x-testers.date-field
                            label="Implementation Date"
                            wire:model="implementation_date" />
                    </div>
                    <div>
                        <x-testers.input-field
                            label="Additional Info"
                            wire:model="additional_info"
                            placeholder="Enter Additional Info..." />
                    </div>
                    <div>
                        <x-testers.input-field
                            label="Linked Measuring Devices"
                            wire:model="linked_measuring_devices"
                            placeholder="Enter Linked Measuring Devices..." />
                    </div>
                    <x-testers.upload-field
                        label="Documents"
                        :multiple="true"
                        model="documents"
                        placeholder="Upload files" />

                    <x-testers.upload-field
                        label="Asset"
                        :multiple="true"
                        model="asset_files"
                        placeholder="Add new asset" />
                </div>
            </div>

            <div class="space-y-6 text-left">
                <div class="p-4 rounded-lg border border-black-100 relative">
                    <label class="block text-sm font-medium text-black-800 mb-2">Search to Copy</label>
                    <input type="text" wire:model.live.debounce.300ms="search_query" placeholder="Search by name or ID..." class="block w-full rounded-full bg-grey text-white placeholder-black-300 px-8 py-4 border-none shadow-none sm:text-sm focus:ring-2 focus:ring-blue-200 focus:outline-none transition">
                    @if(!empty($search_results))
                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-auto">
                        @foreach($search_results as $result)
                        <div class="px-4 py-2 hover:bg-gray-50 flex justify-between items-center border-b">
                            <span class="text-sm text-gray-700">{{ $result['name'] }}</span>
                            <button type="button" wire:click="selectAndCopyTester({{ $result['id'] }})" class="text-xs bg-primary text-white px-2 py-1 rounded-full hover:bg-secondary transition">Copy</button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4">
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
                        <x-testers.dropdown-field
                            label="Location"
                            :options="$locations"
                            placeholder="Select Location..."
                            wire:model="location_id">
                            <option value="new" class="text-blue-600 font-bold">+ Add New Location...</option>
                            </x-dropdown-field>
                    </div>
                    <div>
                        <x-testers.dropdown-field
                            label="Status"
                            :options="$statuses"
                            placeholder=""
                            wire:model="status_id" />
                    </div>
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
                <div>
                    <x-testers.dropdown-field
                        label="Type"
                        :options="$types"
                        placeholder="Select Tester Type..."
                        wire:model="type">
                        <option value="new" class="text-blue-600 font-bold">+ Add New Type...</option>
                        </x-dropdown-field>
                </div>

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
        </div>
    </div>
</div>