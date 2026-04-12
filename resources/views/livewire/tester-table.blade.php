<div class="bg-white shadow-sm rounded-lg p-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">Tester List</h3>
        <div class="flex items-center gap-4">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 pr-4 py-2 w-70 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none"
                    placeholder="Search testers..."
                    style="box-shadow:none;">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#2C3E50]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                    </svg>
                </span>
            </div>
            <button
                class="flex items-center gap-2 text-[#2C3E50] text-lg font-normal bg-transparent border-0 shadow-none hover:text-[#B10530] focus:outline-none"
                type="button"
                @click="alert('Filter functionality coming soon!')">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <line x1="4" y1="6" x2="20" y2="6" stroke-width="2" stroke="currentColor" />
                    <line x1="8" y1="12" x2="16" y2="12" stroke-width="2" stroke="currentColor" />
                    <line x1="10" y1="18" x2="14" y2="18" stroke-width="2" stroke="currentColor" />
                </svg>
                <span>Filter</span>
            </button>

            <button
                class="ml-2 px-4 py-2 rounded-full bg-[#B10530] text-white font-semibold hover:bg-pink-700 transition text-sm"
                wire:click="$dispatch('switchTab', { tab: 'add' })"
                type="button">
                Add Tester
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-[1600px]">
            <thead>
                <tr class="border-b">
                    @foreach ($headers as $header)
                    <th class="px-4 py-2 text-left text-sm text-gray-700 whitespace-nowrap">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($testers as $tester)
                <tr class="border-b last:border-0">
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->product_family ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->owner_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->id_number_by_customer ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->status ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->location_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->type ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->operating_system ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $tester->manufacturer ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">
                        {{ $tester->implementation_date ? \Carbon\Carbon::parse($tester->implementation_date)->format('Y-m-d') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-4 py-6 text-center text-gray-400">No testers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $testers->links() }}
        </div>
    </div>
</div>