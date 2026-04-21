<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] mb-12 md:mb-0 rounded-[24px] md:rounded-2xl bg-white px-5 md:px-10 pt-6 md:pt-8 pb-10 md:pb-12 shadow-[0_2px_10px_rgba(0,0,0,0.02)] md:shadow-sm font-sans text-gray-800">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-8 border-b border-gray-200">
        <div class="flex items-center gap-4 mb-4 md:mb-0">
            <button 
                type="button" 
                wire:click="$dispatch('switchTab', { tab: 'all' })" 
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight">Add New Tester</h2>
        </div>

        <div class="relative flex-1 max-w-md mx-0 md:mx-8 mb-4 md:mb-0">
            <div class="relative w-full lg:w-70">
                <input type="text" wire:model.live.debounce.300ms="search_query" class="w-full pl-10 pr-4 py-2 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none text-sm" placeholder="Search..." style="box-shadow:none;">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#2C3E50]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
            </div>
            @if(!empty($search_results))
            <div class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-auto">
                @foreach($search_results as $result)
                <div class="px-4 py-2 hover:bg-gray-50 flex justify-between items-center border-b cursor-pointer" wire:click="selectAndCopyTester({{ $result['id'] }})">
                    <span class="text-sm text-gray-700 font-medium">{{ $result['id'] }} - {{ $result['name'] }}</span>
                    <span class="text-xs text-gray-400">Copy Data</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <button
            class="bg-primary hover:bg-[#8A0028] text-white text-[15px] font-medium px-8 py-2 md:py-2.5 rounded-full transition-colors flex-shrink-0"
            wire:click="save"
            type="button">
            Save
        </button>
    </div>

    <!-- The Grid Layout -->
    <!-- Matches tester-details layout to maintain white space on the right side -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] md:gap-x-16 pt-2">
        
        <!-- Left Block (Form Inputs) -->
        <div class="flex flex-col pl-0 lg:pl-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-6 lg:gap-y-8 text-left w-full max-w-4xl">
                
                <!-- Row 1 -->
            <div>
                <x-testers.input-field label="*ID" wire:model="tester_id" placeholder="" />
            </div>
            <div>
                <x-testers.input-field label="*Name" wire:model="name" placeholder="" />
            </div>

            <!-- Row 2 -->
            <div class="lg:col-span-2">
                <x-testers.textarea-field label="*Description" wire:model="description" rows="2" placeholder="" />
            </div>

            <!-- Row 3 -->
            <div>
                <x-testers.input-field label="*Customer ID" wire:model="id_number_by_customer" placeholder="" />
            </div>
            <div>
                <x-testers.dropdown-field label="*Owner" :options="$owners" placeholder="" wire:model="owner_id" />
            </div>

            <!-- Row 4 -->
            <div>
                <x-testers.dropdown-field label="*Location" :options="$locations" placeholder="" wire:model="location_id" />
            </div>
            <div>
                <x-testers.dropdown-field label="*Status" :options="$statuses" placeholder="" wire:model="status_id" />
            </div>

            <!-- Row 5 -->
            <div>
                <x-testers.dropdown-field label="*Product Family" :options="$families" placeholder="" wire:model="product_family" />
            </div>
            <div>
                <x-testers.dropdown-field label="*Type" :options="$types" placeholder="" wire:model="type" />
            </div>

            <!-- Row 6 -->
            <div>
                <x-testers.dropdown-field label="*Manufacturer" :options="$manufacturers" placeholder="" wire:model="manufacturer" />
            </div>
            <div>
                <x-testers.dropdown-field label="*Operating System" :options="$os_versions" placeholder="" wire:model="operating_system" />
            </div>

            <!-- Row 7 -->
            <div>
                <x-testers.date-field label="*Implementation Date" wire:model="implementation_date" />
            </div>
            <div>
                <x-testers.input-field label="Additional Info" wire:model="additional_info" placeholder="" />
            </div>

            <!-- Row 8 -->
            <div class="lg:col-span-2">
                <x-testers.input-field label="Linked Measuring Devices" wire:model="linked_measuring_devices" placeholder="" />
            </div>

            <!-- Row 9 -->
            <div>
                <x-testers.input-field label="Asset" wire:model="asset_no" placeholder="" />
                
                <button type="button" class="mt-6 w-[200px] py-2 rounded-full bg-[#f3f4f6] text-[#8e95a2] font-semibold text-sm flex justify-center items-center gap-1 hover:bg-gray-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Asset
                </button>
            </div>
            
            <div>
                <x-testers.upload-field label="Documents" :multiple="true" model="documents" accept=".jpg,.jpeg,.png,.gif,.txt,.pdf,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx" placeholder="Upload Files" />
                
                <div wire:loading wire:target="documents" class="text-xs text-blue-600 mt-1">
                    Uploading files...
                </div>

                @error('documents.*')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                @if(!empty($documents))
                <div class="mt-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                    <p class="text-xs font-semibold text-gray-700 mb-1">Selected files:</p>
                    <ul class="space-y-1">
                        @foreach($documents as $document)
                        <li class="text-xs text-gray-600">{{ $document->getClientOriginalName() }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

        </div>
        </div>

        <!-- Right Block (Empty Space to match tester details layout) -->
        <div class="hidden lg:block"></div>

    </div>
</div>