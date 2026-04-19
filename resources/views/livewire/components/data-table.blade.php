<div class="flex min-h-[calc(100vh-8rem)] w-full min-w-0 flex-col rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold pl-8">{{ $title }}</h3>
        <div class="flex items-center gap-4">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 pr-4 py-2 w-70 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none"
                    placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
                    style="box-shadow:none;">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#2C3E50]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                    </svg>
                </span>
            </div>

            <div x-data="{ open: false }" class="relative">
                <button
                    class="flex items-center gap-2 text-[#2C3E50] text-lg font-normal bg-transparent border-0 shadow-none hover:text-primary focus:outline-none"
                    type="button"
                    @click="open = !open">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <line x1="4" y1="6" x2="20" y2="6" stroke-width="2" stroke="currentColor" />
                        <line x1="8" y1="12" x2="16" y2="12" stroke-width="2" stroke="currentColor" />
                        <line x1="10" y1="18" x2="14" y2="18" stroke-width="2" stroke="currentColor" />
                    </svg>
                    <span>Filter</span>
                </button>
                <div
                    x-show="open"
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
                    style="min-width: 180px;">
                    <div class="px-4 py-2 font-semibold border-b">Filter</div>
                    <ul>
                        @foreach ($filters as $key => $label)
                            <li wire:click="toggleFilter('{{ $key }}')" class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex justify-between items-center">
                                <span>{{ $label }}</span>

                                @if(in_array($key, $activeFilters))
                                    <span>✓</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if($showAddButton)
                <button
                    class="ml-2 px-4 py-2 rounded-full bg-primary text-white font-semibold hover:bg-pink-700 transition text-sm"
                    wire:click="$dispatch('switchTab', { tab: 'add' })"
                    type="button">
                    {{ $addButtonLabel ?? 'Add' }}
                </button>
            @endif
        </div>
    </div>
    <div class="data-table-scroll mt-3 flex-1 w-full overflow-x-auto pb-8">
        <table 
            x-data="{ widths: [] }" 
            x-init="$nextTick(() => { 
                if (widths.length === 0) {
                    widths = Array.from($el.querySelectorAll('th')).map(th => th.offsetWidth);
                }
            })"
            class="min-w-full table-auto"
        >
            <thead>
                <tr class="border-b">
                    @foreach ($headers as $key => $label)
                    <th 
                        class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap"
                        :style="widths[{{ $loop->index }}] ? `min-width: ${widths[{{ $loop->index }}]}px; width: ${widths[{{ $loop->index }}]}px;` : ''"
                    >{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $currentCount = method_exists($data, 'count') ? $data->count() : count($data);
                $perPage = method_exists($data, 'perPage') ? $data->perPage() : $currentCount;
                $emptyRows = max($perPage - $currentCount, 0);
                @endphp

                @forelse ($data as $row)
                <tr class="border-b @if($this->hasDetails) hover:bg-thirdly cursor-pointer transition-colors duration-150 @endif"
                    @if($this->hasDetails)
                        @if($type === 'issues')
                            wire:click="$dispatch('switchTab', { tab: 'solution', id: {{ $row->id }} })"
                        @else
                            wire:click="$dispatch('switchTab', { tab: 'details', id: {{ $row->id }} })"
                        @endif
                    @endif
                >
                    @foreach ($headers as $key => $label)
                    <td class="px-5 py-3 text-sm text-gray-800 align-top {{ ($key === 'explanation' || ($type === 'issues' && $key === 'description') || ($type === 'issue-history' && $key === 'description')) ? '' : 'whitespace-nowrap' }}">
                        @if($type === 'issues' && $key === 'description')
                            <div class="max-w-[340px]">
                                <div x-data="{ expanded: false, showButton: false }"
                                     x-init="$nextTick(() => { showButton = $refs.text.scrollHeight > $refs.text.clientHeight })"
                                     class="relative pr-2">
                                    <div x-ref="text"
                                         :class="expanded ? '' : 'line-clamp-2'"
                                         class="whitespace-pre-line text-gray-800 break-words">{{ data_get($row, $key) ?? '-' }}</div>
                                    <button x-show="showButton"
                                            @click.stop="expanded = !expanded"
                                            class="text-blue-600 hover:text-blue-800 text-xs font-semibold mt-1 focus:outline-none hover:underline"
                                            style="display: none;">
                                        <span x-show="!expanded">Show more</span>
                                        <span x-show="expanded">Show less</span>
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    wire:click.stop="$dispatch('switchTab', { tab: 'solution', id: {{ $row->id }} })"
                                    class="mt-2 inline-flex items-center rounded-full bg-[#efefef] px-3 py-1 text-xs font-semibold text-[#6d6d6d] hover:bg-[#e0e0e0]">
                                    + Add Solution
                                </button>
                            </div>
                        @elseif($key === 'explanation')
                            <div x-data="{ expanded: false, showButton: false }"
                                 x-init="$nextTick(() => { showButton = $refs.text.scrollHeight > $refs.text.clientHeight })"
                                 class="relative pr-2" style="max-width: 300px;">
                                <div x-ref="text"
                                     :class="expanded ? '' : 'line-clamp-2'"
                                     class="whitespace-pre-line text-gray-800 break-words">{{ data_get($row, $key) ?? '-' }}</div>
                                <button x-show="showButton" 
                                        @click.stop="expanded = !expanded" 
                                        class="text-blue-600 hover:text-blue-800 text-xs font-semibold mt-1 focus:outline-none hover:underline"
                                        style="display: none;">
                                    <span x-show="!expanded">Show more</span>
                                    <span x-show="expanded">Show less</span>
                                </button>
                            </div>
                        @else
                            {{ data_get($row, $key) ?? '-' }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-5 py-6 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse

                @for ($i = 0; $i < $emptyRows; $i++)
                <tr class="border-b last:border-0">
                    @foreach ($headers as $key => $label)
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    @endforeach
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="mt-auto pt-4">
        {{ $data->links() }}
    </div>
</div>
