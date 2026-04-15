<div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-4">
            <div class="space-y-6 text-left">
                <h4 class="text-lg font-semibold text-gray-700 border-l-4 border-[#2C3E50] pl-3">Basic Information</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tester ID</label>
                        <input type="text" wire:model="tester_id" placeholder="Enter ID..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tester Name</label>
                        <input type="text" wire:model="name" placeholder="Enter Name..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 text-left">Customer ID</label>
                            <input type="text" wire:model="id_number_by_customer" placeholder="Enter Customer ID..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 text-left">Implementation Date</label>
                            <input type="date" wire:model="implementation_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 text-left">
                <h4 class="text-lg font-semibold text-gray-700 border-l-4 border-blue-500 pl-3">Advanced Configuration</h4>

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
                        <label class="block text-sm font-medium text-gray-700 text-left">Product Family</label>
                        <select wire:model="product_family" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Family...</option>
                            @foreach($families as $family)
                            <option value="{{ $family }}">{{ $family }}</option>
                            @endforeach
                            <option value="new" class="text-blue-600 font-bold">+ Add New Family...</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 text-left">Manufacturer</label>
                        <select wire:model="manufacturer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Manufacturer...</option>
                            @foreach($manufacturers as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                            <option value="new" class="text-blue-600 font-bold">+ Add New Manufacturer...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 text-left">Operating System</label>
                        <select wire:model="operating_system" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select OS...</option>
                            @foreach($os_versions as $os)
                            <option value="{{ $os }}">{{ $os }}</option>
                            @endforeach
                            <option value="new" class="text-blue-600 font-bold">+ Add New OS...</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Owner</label>
                        <select wire:model="owner_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Owner...</option>
                            @foreach($owners as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                            @endforeach
                            <option value="new" class="text-blue-600 font-bold">+ Add New Owner...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <select wire:model="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Location...</option>
                            @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                            @endforeach
                            <option value="new" class="text-blue-600 font-bold">+ Add New Location...</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model="status_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                @foreach($statuses as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <input type="text" wire:model="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
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

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 font-bold">Documents (Images/PDF)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition cursor-pointer relative">
                        <input type="file" wire:model="documents" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="text-gray-400">
                            <span class="text-blue-600 font-semibold">Click to upload</span> or drag and drop
                            <p class="text-xs mt-1">PNG, JPG, PDF up to 10MB</p>
                        </div>
                    </div>
                    <div wire:loading wire:target="documents" class="text-xs text-blue-600 mt-2">Uploading...</div>
                </div>
            </div>
        </div>
    </div>

</div>