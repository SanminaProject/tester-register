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
                    @if (!empty($columnFilters))
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
                    @endif
                </button>
                <div
                    x-show="open"
                    @click.away="open = false"
                    class="absolute right-0 z-20 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl"
                    style="width: min(44rem, calc(100vw - 2rem));">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Filter</div>
                            <div class="text-xs text-gray-500">Use any column to narrow the current list</div>
                        </div>
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="rounded-full bg-[#f3f3f3] px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200">
                            Clear all
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-4">
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($filters as $filter)
                                <div class="rounded-xl border border-gray-200 bg-[#fafafa] p-3">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <span class="text-sm font-semibold text-gray-700">{{ $filter['label'] }}</span>
                                        <span class="text-[11px] uppercase tracking-wide text-gray-400">{{ str_replace('_', ' ', $filter['type']) }}</span>
                                    </div>

                                    @if ($filter['type'] === 'range')
                                        <div class="grid grid-cols-2 gap-2">
                                            <input
                                                type="number"
                                                placeholder="From"
                                                wire:model.live.debounce.250ms="columnFilters.{{ $filter['stateKey'] }}.min"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                                            <input
                                                type="number"
                                                placeholder="To"
                                                wire:model.live.debounce.250ms="columnFilters.{{ $filter['stateKey'] }}.max"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                                        </div>
                                    @elseif ($filter['type'] === 'date_range')
                                        <div class="grid grid-cols-2 gap-2">
                                            <input
                                                type="date"
                                                placeholder="From"
                                                wire:model.live="columnFilters.{{ $filter['stateKey'] }}.from"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                                            <input
                                                type="date"
                                                placeholder="To"
                                                wire:model.live="columnFilters.{{ $filter['stateKey'] }}.to"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                                        </div>
                                    @elseif ($filter['type'] === 'multi')
                                        <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2">
                                            @forelse ($filter['options'] as $option)
                                                <label wire:key="filter-{{ $filter['stateKey'] }}-option-{{ md5((string) $option) }}" class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 hover:bg-gray-50">
                                                    <input
                                                        type="checkbox"
                                                        value="{{ $option }}"
                                                        wire:model.live="columnFilters.{{ $filter['stateKey'] }}"
                                                        class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary">
                                                    <span class="break-words text-sm text-gray-700">{{ $option }}</span>
                                                </label>
                                            @empty
                                                <div class="px-2 py-1.5 text-xs text-gray-400">No selectable values</div>
                                            @endforelse
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            placeholder="Filter {{ $filter['label'] }}"
                                            wire:model.live.debounce.300ms="columnFilters.{{ $filter['stateKey'] }}"
                                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <button
                type="button"
                wire:click="exportCurrentList"
                wire:loading.attr="disabled"
                wire:target="exportCurrentList"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-[#f5f5f5] px-4 py-2 text-sm font-medium text-gray-600 shadow-none transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                </svg>
                <span>Export current list</span>
            </button>
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
