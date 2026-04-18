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
            <h2 class="text-xl font-extrabold text-black tracking-tight">Fixture Details</h2>
        </div>

        <x-primary-button type="button" class="w-32">
            Edit
        </x-primary-button>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:gap-x-16 gap-y-7 md:gap-y-10 border-b md:border-none pb-6 md:pb-0 border-[#e8e8e8]">
        
        <!-- Mobile specific top nav block (hidden on desktop) -->
        <div class="md:hidden flex items-center justify-between pb-5 border-b border-[#e8e8e8]">
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    wire:click="$dispatch('switchTab', { tab: 'all' })" 
                    class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h2 class="text-[18px] font-extrabold text-black">Fixture Details</h2>
            </div>
        </div>

        <!-- 2. Main Info Block -->
        <div class="flex flex-col gap-y-2 lg:gap-y-3.5 mt-0 lg:mt-2 pl-0 lg:pl-12 w-full lg:max-w-4xl">
            @php
            $rows = [
                'ID' => $fixture->id,
                'Name' => $fixture->name,
                'Description' => $fixture->description,
                'Manufacturer' => $fixture->manufacturer,
                'Tester ID' => $fixture->tester_id,
                'Tester' => $fixture->tester?->name,
                'Location' => $fixture->location?->name,
                'Status' => $fixture->status?->name ? strtoupper($fixture->status->name) : null,
            ];
            @endphp
            
            @foreach($rows as $label => $value)
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-dark-grey tracking-wide text-[14px] md:text-[16px]">{{ $label }}</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] whitespace-pre-line leading-[24px] md:leading-relaxed">{{ $value ?? '-' }}@if($label === 'Status')<span class="inline-block w-[14px] md:w-2.5 h-[14px] md:h-2.5 rounded-full {{ strtolower($fixture->status?->name ?? '') === 'active' ? 'bg-[#31c03b]' : 'bg-red-500' }} ml-1.5 md:ml-1.5 align-text-bottom md:align-baseline mb-[3px] md:mb-0"></span>@endif</div>
            </div>
            @endforeach
        </div>

        <!-- Mobile button for edit, as there are no right column blocks -->
        <div class="md:hidden pt-4">
           <x-primary-button type="button" class="w-full justify-center !py-3 bg-[#1a1a1a]">
               Edit
           </x-primary-button>
        </div>
    </div>
</div>
