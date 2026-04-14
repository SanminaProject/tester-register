<div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h3 class="text-xl font-bold text-[#2C3E50]">Tester Details: {{ $tester->name }}</h3>
        <button
            class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition text-sm"
            wire:click="$dispatch('switchTab', { tab: 'all' })"
            type="button">
            Back to List
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">ID</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->id }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Name</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->name ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Description</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->description ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Prod Family</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->product_family ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Owner</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->owner?->name ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Customer ID</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->id_number_by_customer ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Status</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->statusRelation?->name ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Location</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->location?->name ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Type</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->type ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Manufacturer</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->manufacturer ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">OS</span>
            <span class="text-base text-[#2C3E50]">{{ $tester->operating_system ?? '-' }}</span>
        </div>
        
        <div class="flex flex-col gap-1">
            <span class="text-sm text-gray-500 font-medium">Implementation Date</span>
            <span class="text-base text-[#2C3E50]">
                {{ $tester->implementation_date ? $tester->implementation_date->format('Y-m-d') : '-' }}
            </span>
        </div>
    </div>
</div>
