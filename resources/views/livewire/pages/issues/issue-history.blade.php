<div class="flex min-h-[calc(100vh-8rem)] w-full min-w-0 flex-col rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold pl-8">Issue History</h3>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 pr-4 py-2 w-70 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none"
                    placeholder="Search issue history..."
                    style="box-shadow:none;">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#2C3E50]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            @if(in_array($key, $activeFilters, true))
                            <span>✓</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <button
                class="ml-1 px-4 py-2 rounded-full bg-primary text-white font-semibold hover:bg-pink-700 transition text-sm"
                wire:click="beginAddIssue"
                type="button">
                Add Issue
            </button>
        </div>
    </div>

    <div class="data-table-scroll mt-3 flex-1 w-full overflow-x-auto pb-8">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="border-b">
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">Log ID</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">Date</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">Tester ID</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">Type</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700">Description</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">User</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->groups as $issue)
                <tr class="border-b">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->date?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->tester_id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">Problem</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <div class="max-w-[340px]">
                            <div x-data="{ expanded: false, showButton: false }"
                                x-init="$nextTick(() => { showButton = $refs.text.scrollHeight > $refs.text.clientHeight })"
                                class="relative pr-2">
                                <div x-ref="text"
                                    :class="expanded ? '' : 'line-clamp-2'"
                                    class="whitespace-pre-line text-gray-800 break-words">{{ $issue->description ?? '-' }}</div>
                                <button x-show="showButton"
                                    @click.stop="expanded = !expanded"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-semibold mt-1 focus:outline-none hover:underline"
                                    style="display: none;">
                                    <span x-show="!expanded">Show more</span>
                                    <span x-show="expanded">Show less</span>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->createdBy?->full_name ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        @php
                        $statusName = strtolower((string) ($issue->issueStatusRelation?->name ?? ''));
                        $isSolved = $statusName === 'solved';
                        $isActive = $statusName === 'active';
                        @endphp
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $isSolved ? 'bg-[#CFF3DA] text-[#2E9F57]' : ($isActive ? 'bg-[#FFD8DE] text-[#FF4A5A]' : 'bg-gray-200 text-gray-700') }}">
                            {{ strtoupper($issue->issueStatusRelation?->name ?? '-') }}
                        </span>
                    </td>
                </tr>

                @foreach ($issue->solutionEntries as $solution)
                <tr class="border-b">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">&nbsp;</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $solution->date?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">&nbsp;</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">Solution</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <div class="max-w-[340px]">
                            <div x-data="{ expanded: false, showButton: false }"
                                x-init="$nextTick(() => { showButton = $refs.text.scrollHeight > $refs.text.clientHeight })"
                                class="relative pr-2">
                                <div x-ref="text"
                                    :class="expanded ? '' : 'line-clamp-2'"
                                    class="whitespace-pre-line text-gray-800 break-words">{{ $solution->resolution_description ?? $solution->description ?? '-' }}</div>
                                <button x-show="showButton"
                                    @click.stop="expanded = !expanded"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-semibold mt-1 focus:outline-none hover:underline"
                                    style="display: none;">
                                    <span x-show="!expanded">Show more</span>
                                    <span x-show="expanded">Show less</span>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $solution->resolvedBy?->full_name ?? $solution->createdBy?->full_name ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        @php
                        $solutionStatusName = strtolower((string) ($solution->issueStatusRelation?->name ?? ''));
                        $solutionIsSolved = $solutionStatusName === 'solved';
                        $solutionIsActive = $solutionStatusName === 'active';
                        @endphp
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $solutionIsSolved ? 'bg-[#CFF3DA] text-[#2E9F57]' : ($solutionIsActive ? 'bg-[#FFD8DE] text-[#FF4A5A]' : 'bg-gray-200 text-gray-700') }}">
                            {{ strtoupper($solution->issueStatusRelation?->name ?? '-') }}
                        </span>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-6 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-auto pt-4">
        {{ $this->groups->links() }}
    </div>
</div>