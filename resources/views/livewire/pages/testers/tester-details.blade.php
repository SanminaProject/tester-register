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

        <button type="button" class="bg-primary text-white px-8 py-2.5 rounded-full font-semibold text-[13px] hover:opacity-90 transition">
            Edit
        </button>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] xl:grid-cols-[1fr_400px] gap-x-16 gap-y-10">
        
        <!-- 2. Left Block (Main Info) -->
        <div class="flex flex-col gap-y-3.5 mt-2">
            @php
            $rows = [
                'ID' => $tester->id ?? '160',
                'Name' => $tester->name ?? 'TAKAYA FLYING PROBE APT 8400CE',
                'Description' => $tester->description ?? 'FLYING PROBE TESTER',
                'Customer ID' => $tester->id_number_by_customer ?? 'SN 9708037',
                'Status' => strtoupper($tester->statusRelation?->name ?? 'ACTIVE'),
                'Product Family' => $tester->product_family ?? 'ALL',
                'Owner' => $tester->owner?->name ?? 'SANMINA',
                'Location' => $tester->location?->name ?? 'PROTO',
                'Type' => $tester->type ?? 'FLYING PRO',
                'Operating System' => $tester->operating_system ?? 'XP/APT4.13',
                'Manufacturer' => $tester->manufacturer ?? 'TAKAYA',
                'Implementation Date' => $tester->implementation_date ? $tester->implementation_date->format('j.n.Y H:i') : '1.1.2003 0:00',
                'Linked Measuring Devices' => '\N',
                'Additional Info' => '\N',
                'Asset' => "\N\n\N\n\N"
            ];
            @endphp
            
            @foreach($rows as $label => $value)
            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey text-[13px]">{{ $label }}</div>
                <div class="text-black font-extrabold text-[13px] whitespace-pre-line leading-relaxed">{{ $value ?? '-' }}@if($label === 'Status')<span class="inline-block w-2.5 h-2.5 rounded-full {{ strtolower($tester->statusRelation?->name ?? '') === 'active' ? 'bg-green-500' : 'bg-red-500' }} ml-1.5 align-baseline"></span>@endif</div>
            </div>
            @endforeach
        </div>

        <!-- 3. Right Blocks (Inventory & Links/Docs) -->
        <div class="flex flex-col gap-6">
            
            <!-- Inventory Block -->
            <div class="bg-light-grey rounded-xl p-7">
                <p class="text-[13px] text-dark-grey mb-1.5">Last Inventoried Date:</p>
                <p class="text-[13px] text-dark-grey mb-7">6/4/2026 13:54</p>
                <button type="button" class="w-full bg-primary text-white px-6 py-2.5 rounded-full font-semibold text-[13px] hover:opacity-90 transition">
                    Inventory
                </button>
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
        
        <div class="flex flex-col gap-y-3.5">
            <div class="grid grid-cols-[100px_1fr_100px_1fr] items-center gap-x-4">
                <span class="text-dark-grey text-[13px]">Log ID</span>
                <span class="text-black text-[13px] font-extrabold max-w-[100px]">1876</span>
                <span class="text-dark-grey text-[13px]">Detector</span>
                <span class="text-black text-[13px] font-extrabold">M.Kallio</span>
            </div>

            <div class="grid grid-cols-[100px_1fr] items-center gap-x-4 mt-2">
                <span class="text-dark-grey text-[13px]">Entry Date</span>
                <span class="text-black text-[13px] font-extrabold">2.10.2025 14:54</span>
            </div>

            <div class="flex flex-col gap-1.5 mt-2">
                <div class="grid grid-cols-[100px_1fr] items-center gap-x-4">
                    <span class="text-dark-grey text-[13px]">Indication</span>
                    <span class="text-black text-[13px] font-extrabold">Kalibrointi ja yllapitohuolto</span>
                </div>
            </div>

            <div class="grid grid-cols-[100px_1fr] items-center gap-x-4 mt-4">
                <span class="text-dark-grey text-[13px]">Solved Date</span>
                <span class="text-black text-[13px] font-extrabold">2.10.2025 14:54</span>
            </div>

            <div class="flex flex-col gap-1.5 mt-2">
                <div class="grid grid-cols-[100px_1fr] items-start gap-x-4">
                    <span class="text-dark-grey text-[13px] pt-0.5">Solution</span>
                    <span class="text-black text-[13px] font-extrabold leading-relaxed pr-10">vaihdetta probet #1 #4, Ohjelmat varmuuskopioitu verkkoasemalle: testerbackup \\143.116.232.215\TAKAYA1</span>
                </div>
            </div>
            
        </div>
    </div>

</div>
