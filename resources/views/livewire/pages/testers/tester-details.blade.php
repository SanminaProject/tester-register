<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] rounded-2xl bg-white px-10 pt-8 pb-12 shadow-sm font-sans text-gray-800">
    <!-- 1. Header (Title & Edit Button) -->
    <div class="flex items-center justify-between pb-6 mb-8 border-b border-gray-200">
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
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] gap-x-16 gap-y-10">
        
        <!-- 2. Left Block (Main Info) -->
        <div class="flex flex-col gap-y-3.5 mt-2 pl-12">
            @php
            $rows = [
                'ID' => $tester->id,
                'Name' => $tester->name,
                'Description' => $tester->description,
                'Customer ID' => $tester->id_number_by_customer,
                'Status' => $tester->statusRelation ? strtoupper($tester->statusRelation->name) : null,
                'Product Family' => $tester->product_family,
                'Owner' => $tester->owner?->name,
                'Location' => $tester->location?->name,
                'Type' => $tester->type,
                'Operating System' => $tester->operating_system,
                'Manufacturer' => $tester->manufacturer,
                'Implementation Date' => $tester->implementation_date ? $tester->implementation_date->format('j.n.Y H:i') : null,
            ];
            @endphp
            
            @foreach($rows as $label => $value)
            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey text-[16px]">{{ $label }}</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $value ?? '-' }}@if($label === 'Status')<span class="inline-block w-2.5 h-2.5 rounded-full {{ strtolower($tester->statusRelation?->name ?? '') === 'active' ? 'bg-green-500' : 'bg-red-500' }} ml-1.5 align-baseline"></span>@endif</div>
            </div>
            @endforeach
        </div>

        <!-- 3. Right Blocks (Inventory & Links/Docs) -->
        <div class="flex flex-col gap-6">
            
            <!-- Inventory Block -->
            <div class="bg-light-grey rounded-xl p-7 flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="text-[15px] text-dark-grey">Last Inventoried Date:</span>
                    <span class="text-[16px] text-dark-grey">{{ $tester->last_inventoried_date ? $tester->last_inventoried_date->format('j.n.Y H:i') : 'Never' }}</span>
                </div>
                <x-primary-button type="button" class="w-40" wire:click="updateInventoryDate">
                    Inventory
                </x-primary-button>
            </div>

            <!-- Links & Docs Block -->
            <div class="bg-light-grey rounded-xl p-7 flex flex-col gap-6">
                <!-- Links -->
                <div class="flex flex-col gap-5 border-b border-gray-200 pb-6">
                    @foreach(['Maintenance /Calibration', 'Spare Parts', 'Audit Logs'] as $link)
                    <button class="flex justify-between items-center text-[13px] font-extrabold text-black hover:text-primary transition group">
                        {{ $link }}
                        <svg class="h-4 w-4 text-black group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    @endforeach
                </div>

                <!-- Documents -->
                <div class="flex flex-col gap-5 pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[13px] font-extrabold text-black">Doucuments</span>
                        <button class="text-[11px] text-dark-grey hover:text-black flex items-center gap-1.5 transition">
                            <svg class="h-[14px] w-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download All
                        </button>
                    </div>

                    <div class="flex flex-col gap-4">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="flex justify-between items-center">
                            <a href="#" class="text-[12px] text-gray-400 underline decoration-gray-300 underline-offset-[3px] hover:text-black hover:decoration-black transition">
                                Document_{{ $i }}.pdf
                            </a>
                            <button class="text-gray-400 hover:text-black transition">
                                <svg class="h-[15px] w-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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
    <div class="mt-12 bg-light-grey rounded-xl p-8">
        <h3 class="text-[13px] text-dark-grey border-b border-gray-200 pb-4 mb-6">Issues</h3>
        
        @php
            $issues = \App\Models\TesterEventLog::where('tester_id', $tester->id)->orderBy('date', 'desc')->get();
        @endphp

        <div class="flex flex-col gap-y-10">
            @forelse($issues as $issue)
            <div class="flex flex-col gap-y-3.5 {{ !$loop->last ? 'border-b border-gray-200 pb-8' : '' }}">
                <div class="grid grid-cols-[100px_1fr_100px_1fr] items-center gap-x-4">
                    <span class="text-dark-grey text-[13px]">Log ID</span>
                    <span class="text-black text-[13px] font-extrabold max-w-[100px]">{{ $issue->id }}</span>
                    <span class="text-dark-grey text-[13px]">Detector</span>
                    <span class="text-black text-[13px] font-extrabold">{{ \App\Models\User::find($issue->created_by_user_id)?->name ?? 'Unknown' }}</span>
                </div>

                <div class="grid grid-cols-[100px_1fr] items-center gap-x-4 mt-2">
                    <span class="text-dark-grey text-[13px]">Entry Date</span>
                    <span class="text-black text-[13px] font-extrabold">{{ $issue->date ? $issue->date->format('j.n.Y H:i') : '-' }}</span>
                </div>

                <div class="flex flex-col gap-1.5 mt-2">
                    <div class="grid grid-cols-[100px_1fr] items-center gap-x-4">
                        <span class="text-dark-grey text-[13px]">Indication</span>
                        <span class="text-black text-[13px] font-extrabold">{{ $issue->description ?? '-' }}</span>
                    </div>
                </div>

                @if($issue->resolved_date || $issue->resolution_description)
                <div class="grid grid-cols-[100px_1fr] items-center gap-x-4 mt-4">
                    <span class="text-dark-grey text-[13px]">Solved Date</span>
                    <span class="text-black text-[13px] font-extrabold">{{ $issue->resolved_date ? $issue->resolved_date->format('j.n.Y H:i') : '-' }}</span>
                </div>

                <div class="flex flex-col gap-1.5 mt-2">
                    <div class="grid grid-cols-[100px_1fr] items-start gap-x-4">
                        <span class="text-dark-grey text-[13px] pt-0.5">Solution</span>
                        <span class="text-black text-[13px] font-extrabold leading-relaxed pr-10">{{ $issue->resolution_description ?? '-' }}</span>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-[13px] text-dark-grey py-4">No issues recorded for this tester.</div>
            @endforelse
        </div>
    </div>

</div>
