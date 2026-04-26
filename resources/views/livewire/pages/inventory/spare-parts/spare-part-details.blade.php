<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] rounded-2xl bg-white px-10 pt-8 pb-12 shadow-sm font-sans text-gray-800">
    <!-- 1. Header (Title & Edit Button) -->
    <div class="flex items-center justify-between pb-6 mb-8 border-b border-gray-200">
        <div class="flex items-center gap-4">
            <!-- Back Arrow -->
            <button 
                type="button" 
                wire:click="$dispatch('switchTab', { tab: 'spare-parts' })" 
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight">Spare Part Details</h2>
        </div>

        <x-primary-button type="button" class="w-32" wire:click="$dispatch('switchTab', { tab: 'edit-spare-parts', id: {{ $sparePart->id }} })">
            Edit
        </x-primary-button>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-y-10">
        
        <!-- 2. Main Info Block -->
        <div class="flex flex-col gap-y-3.5 pl-12 w-full max-w-4xl">
            @php
            $rows = [
                'ID' => $sparePart->id,
                'Stock Status' => $sparePart->needs_reorder,
                'Name' => $sparePart->name,
                'Description' => $sparePart->description,
                'Manufacturer Part Number' => $sparePart->manufacturer_part_number,
                'In Stock' => $sparePart->quantity_in_stock,
                'Unit Price' => $sparePart->unit_price,
                'Last Ordered' => $sparePart->last_order_date,
                'Tester ID' => $sparePart->tester_id,
                'Tester Name' => $sparePart->tester?->name,
                'Supplier ID' => $sparePart->supplier_id,
                'Supplier Name' => $sparePart->supplier?->supplier_name,
                'Responsible Users' => $sparePart->responsible_user_names,
                'Responsible Users for Associated Tester' => $sparePart->tester_responsible_user_names,
                'Reorder Level' => $sparePart->reorder_level,
            ];
            @endphp

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-center mb-4">
                <div class="text-dark-grey tracking-wide text-[16px]">Stock Status</div>
                <div>
                    <x-status-badge :status="$sparePart->needs_reorder ? 'reorder' : 'in stock'" />
                </div>
            </div>
            
            @foreach($rows as $label => $value)
                @continue($label === 'Stock Status')
                <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                    <div class="text-dark-grey tracking-wide text-[16px]">{{ $label }}</div>
                    <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $value ?? '-' }}@if($label === 'Status')<span class="inline-block w-2.5 h-2.5 rounded-full {{ strtolower($fixture->status?->name ?? '') === 'active' ? 'bg-[#31c03b]' : 'bg-red-500' }} ml-1.5 align-baseline"></span>@endif</div>
                </div>
            @endforeach
        </div>

        @if(auth()->user() && auth()->user()->hasRole('Admin'))
        <div class="mt-16 pl-10 flex justify-start gap-4">
            <button 
                type="button" 
                wire:click="deleteSparePart"
                wire:confirm="Are you sure you want to delete this spare part? This action cannot be undone."
                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg font-semibold text-[14px] transition-colors shadow-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Part
            </button>

            <x-primary-button
                type="button"
                wire:click="$dispatch('switchTab', { tab: 'email-form', sparePartId: {{ $sparePart->id }} })"
                class="w-32">
                Send Email
            </x-primary-button>
        </div>
        @endif
    </div>
</div>
