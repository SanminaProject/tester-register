<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] mb-12 md:mb-0 rounded-[24px] md:rounded-2xl bg-white px-5 md:px-10 pt-6 md:pt-8 pb-10 md:pb-12 shadow-[0_2px_10px_rgba(0,0,0,0.02)] md:shadow-sm font-sans text-gray-800">
    <!-- 1. Header (Title & Edit Button) -->
    <div class="hidden md:flex items-center justify-between pb-6 mb-8 border-b border-gray-200">
        <div class="flex items-center gap-4">
            <!-- Back Arrow -->
            <button 
                type="button" 
                wire:click="$dispatch('switchTab', { tab: 'all' })" 
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight">Tester Details</h2>
        </div>

        <x-primary-button type="button" class="w-32">
            Edit
        </x-primary-button>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] md:gap-x-16 gap-y-7 md:gap-y-10">

        <!-- Mobile specific top inventory block (hidden on desktop) -->
        <div class="md:hidden flex items-center justify-between pb-5 border-b border-[#e8e8e8]">
            <div class="flex flex-col">
                <span class="tracking-wide text-[14px] text-[#8c8c8c] mb-1">Last Inventoried:</span>
                <span class="text-[14px] text-[#8c8c8c]">{{ $tester->last_inventoried_date ? $tester->last_inventoried_date->format('j/n/Y H:i') : '5/4/2026 15:23' }}</span>
            </div>
            <button type="button" class="bg-[#C11232] hover:bg-red-800 text-white text-[15px] font-medium px-6 py-2.5 rounded-full transition-colors" wire:click="updateInventoryDate">
                Inventory
            </button>
        </div>
        
        <!-- 2. Left Block (Main Info) -->
        <div class="flex flex-col gap-y-2 lg:gap-y-3.5 mt-0 lg:mt-2 pl-0 lg:pl-12">
            @php
            $rows = [
                'ID' => $tester->id,
                'Name' => $tester->name,
                'Description' => $tester->description,
                'Status' => $tester->statusRelation ? strtoupper($tester->statusRelation->name) : null,
                'Location' => $tester->location?->name,
                'Owner' => $tester->owner?->name,
                'Customer ID' => $tester->id_number_by_customer,
                'Operation System' => $tester->operating_system,
                'Type' => $tester->type,
                'Product Family' => $tester->product_family,
                'Manufacturer' => $tester->manufacturer,
                'Implementaion Date' => $tester->implementation_date ? $tester->implementation_date->format('j.n.Y H:i') : null,
                'Linked Measuring Devices' => $tester->linked_measuring_devices ?? null,
                'Additional Info' => $tester->additional_info,
                'Asset 1' => $tester->asset_1,
                'Asset 2' => $tester->asset_2,
                'Asset 3' => $tester->asset_3,
                'Asset 4' => $tester->asset_4,
                'Asset 5' => $tester->asset_5,
            ];

            // Filter out empty asset rows just like requested
            $rows = array_filter($rows, function($value, $label) {
                if (in_array($label, ['Linked Measuring Devices', 'Additional Info']) || str_starts_with($label, 'Asset ')) {
                    return $value !== null && $value !== '';
                }
                return true; // Keep all other regular fields
            }, ARRAY_FILTER_USE_BOTH);
            @endphp
            
            @foreach($rows as $label => $value)
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-dark-grey tracking-wide text-[14px] md:text-[16px]">{{ $label }}</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] whitespace-pre-line leading-[24px] md:leading-relaxed">{{ $value ?? '-' }}@if($label === 'Status')<span class="inline-block w-[14px] md:w-2.5 h-[14px] md:h-2.5 rounded-full {{ strtolower($tester->statusRelation?->name ?? '') === 'active' ? 'bg-[#31c03b]' : 'bg-red-500' }} ml-1.5 md:ml-1.5 align-text-bottom md:align-baseline mb-[3px] md:mb-0"></span>@endif</div>
            </div>
            @endforeach
        </div>

        <!-- 3. Right Blocks (Inventory & Links/Docs) -->
        <div class="hidden md:flex flex-col gap-5 md:gap-6">
            
            <!-- Inventory Block -->
            <div class="bg-[#f8f8f8] md:bg-light-grey rounded-[16px] md:rounded-xl p-5 md:p-7 flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="tracking-wide text-[12px] md:text-[15px] text-[#8c8c8c] md:text-dark-grey">Last Inventoried Date:</span>
                    <span class="font-semibold md:font-normal text-[13px] md:text-[16px] text-[#1a1a1a] md:text-dark-grey">{{ $tester->last_inventoried_date ? $tester->last_inventoried_date->format('j.n.Y H:i') : 'Never' }}</span>
                </div>
                <x-primary-button type="button" class="w-32 md:w-40 text-[13px] !py-2.5 md:text-sm">
                    Inventory
                </x-primary-button>
            </div>

            <!-- Links & Docs Block -->
            <div class="bg-[#f8f8f8] md:bg-light-grey rounded-[16px] md:rounded-xl p-5 md:p-7 flex flex-col gap-5 md:gap-6">
                <!-- Links -->
                <div class="flex flex-col gap-4 md:gap-5 border-b border-[#e8e8e8] md:border-gray-200 pb-5 md:pb-6">
                    @foreach(['Maintenance /Calibration', 'Spare Parts', 'Audit Logs'] as $link)
                    <button class="flex justify-between items-center text-[13px] font-semibold md:font-extrabold text-[#1a1a1a] md:text-black hover:text-primary transition group">
                        {{ $link }}
                        <svg class="h-[14px] w-[14px] md:h-4 md:w-4 text-[#8c8c8c] md:text-black group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    @endforeach
                </div>

                <!-- Documents -->
                <div class="flex flex-col gap-4 md:gap-5 pt-0 md:pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[13px] font-semibold md:font-extrabold text-[#1a1a1a] md:text-black">Documents</span>
                        <button class="text-[11px] text-[#8c8c8c] md:text-dark-grey hover:text-black flex items-center gap-1.5 transition uppercase md:capitalize tracking-wider md:tracking-normal font-semibold md:font-normal">
                            <svg class="h-[14px] w-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download All
                        </button>
                    </div>

                    <div class="flex flex-col gap-3 md:gap-4">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="flex justify-between items-center">
                            <a href="#" class="font-semibold md:font-normal text-[13px] md:text-[12px] text-[#2c6ecb] md:text-gray-400 md:underline decoration-gray-300 underline-offset-[3px] hover:text-blue-800 md:hover:text-black hover:decoration-black transition">
                                Document_{{ $i }}.pdf
                            </a>
                            <button class="text-[#8c8c8c] md:text-gray-400 hover:text-black transition">
                                <svg class="h-[15px] w-[15px] md:h-[15px] md:w-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 4. Bottom Block (Issues) -->
    <div class="mt-8 md:mt-16 px-0 md:px-12 pb-0 md:pb-12 w-full lg:max-w-5xl">
        <h3 class="hidden md:block text-lg font-extrabold text-dark-grey border-b border-gray-200 pb-4 mb-6">Issues</h3>
        
        <div class="md:hidden border-t-[1px] border-[#e8e8e8] w-full mb-6"></div>

        @php
            $issues = \App\Models\TesterEventLog::where('tester_id', $tester->id)->orderBy('date', 'desc')->get();
        @endphp

        <div class="flex flex-col gap-y-10 md:px-0">
            @forelse($issues as $issue)
            <div class="flex flex-col gap-y-3 {{ !$loop->last ? 'border-b md:border-gray-200 border-[#e8e8e8] pb-8' : '' }}">
                <div class="grid grid-cols-[140px_1fr] md:grid-cols-[140px_1fr_100px_1fr] items-center gap-x-4 border-b border-[#e8e8e8] md:border-gray-100 pb-3">
                    <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px]">Log ID</span>
                    <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium">{{ $issue->id }}</span>
                    <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px] mt-2 md:mt-0">Detector</span>
                    <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium mt-2 md:mt-0">{{ \App\Models\User::find($issue->created_by_user_id)?->name ?? 'Unknown' }}</span>
                </div>

                <div class="flex flex-col {{ ($issue->resolved_date || $issue->resolution_description) ? 'border-b border-[#e8e8e8] md:border-gray-100 pb-3' : '' }}">
                    <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 mb-2.5">
                        <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px]">Entry Date</span>
                        <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium">{{ $issue->date ? $issue->date->format('j.n.Y H:i') : '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[140px_1fr] items-start gap-x-4">
                        <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px]">Indication</span>
                        <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium whitespace-pre-line leading-[22px] md:leading-relaxed">{{ $issue->description ?? '-' }}</span>
                    </div>
                </div>

                @if($issue->resolved_date || $issue->resolution_description)
                <div class="flex flex-col pt-1">
                    <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 mb-2.5">
                        <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px]">Solved Date</span>
                        <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium">{{ $issue->resolved_date ? $issue->resolved_date->format('j.n.Y H:i') : '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[140px_1fr] items-start gap-x-4">
                        <span class="text-[#8c8c8c] md:text-dark-grey tracking-wide md:tracking-normal text-[13px] md:text-[15px]">Solution</span>
                        <span class="text-[#1a1a1a] md:text-black text-[13px] md:text-[16px] font-semibold md:font-medium whitespace-pre-line leading-[22px] md:leading-relaxed">{{ $issue->resolution_description ?? '-' }}</span>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-[13px] md:text-[16px] text-dark-grey py-4">No issues recorded for this tester.</div>
            @endforelse
        </div>
    </div>

    <!-- Floating Scan Button for Mobile only -->
    <x-mobile-scan-button />

</div>
