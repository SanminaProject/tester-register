<div class="flex min-h-[calc(100vh-8rem)] w-full min-w-0 flex-col rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold pl-8">{{ $this->headerTitle }}</h3>
        <div class="flex items-center gap-3">
            @if ($mode === 'active')
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 pr-4 py-2 w-70 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none"
                    placeholder="Search issues..."
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
            @endif

            @if ($mode !== 'active')
            <button
                class="ml-1 px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition text-sm"
                wire:click="cancelInlineForm"
                type="button">
                Cancel
            </button>
            @endif

            <button
                class="ml-1 px-4 py-2 rounded-full bg-primary text-white font-semibold hover:bg-pink-700 transition text-sm"
                wire:click="{{ $this->mainButtonAction }}"
                type="button">
                {{ $this->mainButtonLabel }}
            </button>
        </div>
    </div>

    @if (session()->has('message'))
    <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('message') }}
    </div>
    @endif

    <div class="data-table-scroll mt-3 flex-1 w-full overflow-x-auto pb-8">
        <table class="min-w-full table-auto"
            x-data="{ widths: [] }"
            x-init="$nextTick(() => {
                if (widths.length === 0) {
                    widths = Array.from($el.querySelectorAll('th')).map(th => th.offsetWidth);
                }
            })">
            <thead>
                <tr class="border-b">
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[0] ? `min-width: ${widths[0]}px; width: ${widths[0]}px;` : ''">Log ID</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[1] ? `min-width: ${widths[1]}px; width: ${widths[1]}px;` : ''">Date</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[2] ? `min-width: ${widths[2]}px; width: ${widths[2]}px;` : ''">Tester ID</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[3] ? `min-width: ${widths[3]}px; width: ${widths[3]}px;` : ''">Type</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700" :style="widths[4] ? `min-width: ${widths[4]}px; width: ${widths[4]}px;` : ''">Description</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[5] ? `min-width: ${widths[5]}px; width: ${widths[5]}px;` : ''">User</th>
                    <th class="px-5 py-3 text-left text-sm text-gray-700 whitespace-nowrap" :style="widths[6] ? `min-width: ${widths[6]}px; width: ${widths[6]}px;` : ''">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                $currentCount = method_exists($this->rows, 'count') ? $this->rows->count() : count($this->rows);
                $perPage = method_exists($this->rows, 'perPage') ? $this->rows->perPage() : $currentCount;
                $baseExtraRows = max($perPage - $currentCount, 0);
                $extraInlineRows = ($showInlineForm && ($mode === 'add_issue' || $mode === 'add_solution')) ? 1 : 0;
                $emptyRows = max($baseExtraRows - $extraInlineRows, 0);
                @endphp

                @if ($showInlineForm && $mode === 'add_issue')
                <tr class="border-b bg-[#faf7f8]">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        <span class="rounded-full bg-[#efefef] px-4 py-1.5 text-xs font-semibold text-[#777]">Auto</span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-text-input type="datetime-local" wire:model="issueForm.date" class="w-full rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('issueForm.date')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="issueForm.tester_id" :options="$testers" placeholder="Select tester" class="rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('issueForm.tester_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">Problem</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <x-text-input type="text" wire:model="issueForm.description" class="w-full rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('issueForm.description')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="issueForm.created_by_user_id" :options="$users" placeholder="Select user" class="rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('issueForm.created_by_user_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide bg-[#FFD8DE] text-[#FF4A5A]">
                            ACTIVE
                        </span>
                    </td>
                </tr>
                @endif

                @forelse ($this->rows as $row)
                <tr
                    class="border-b hover:bg-thirdly cursor-pointer transition-colors duration-150"
                    wire:click="beginAddSolution({{ $row->id }})">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->date?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->tester_id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ ucfirst(strtolower((string) ($row->eventType?->name ?? '-'))) }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <div class="max-w-[340px]">
                            <div x-data="{ expanded: false, showButton: false }"
                                x-init="$nextTick(() => { showButton = $refs.text.scrollHeight > $refs.text.clientHeight })"
                                class="relative pr-2">
                                <div x-ref="text"
                                    :class="expanded ? '' : 'line-clamp-2'"
                                    class="whitespace-pre-line text-gray-800 break-words">{{ $row->description ?? '-' }}</div>
                                <button x-show="showButton"
                                    @click.stop="expanded = !expanded"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-semibold mt-1 focus:outline-none hover:underline"
                                    style="display: none;">
                                    <span x-show="!expanded">Show more</span>
                                    <span x-show="expanded">Show less</span>
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click.stop="beginAddSolution({{ $row->id }})"
                            class="mt-2 inline-flex items-center rounded-full bg-[#efefef] px-3 py-1 text-xs font-semibold text-[#6d6d6d] hover:bg-[#e0e0e0]">
                            + Add Solution
                        </button>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        {{ $row->createdBy?->full_name ?? ($this->userLabelById[$row->created_by_user_id] ?? '-') }}
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        @php
                        $statusName = strtolower((string) ($row->issueStatusRelation?->name ?? ''));
                        $isSolved = $statusName === 'solved';
                        $isActive = $statusName === 'active';
                        @endphp
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $isSolved ? 'bg-[#CFF3DA] text-[#2E9F57]' : ($isActive ? 'bg-[#FFD8DE] text-[#FF4A5A]' : 'bg-gray-200 text-gray-700') }}">
                            {{ strtoupper($row->issueStatusRelation?->name ?? '-') }}
                        </span>
                    </td>
                </tr>

                @if ($showInlineForm && $mode === 'add_solution' && $selectedIssueId === (int) $row->id)
                <tr class="border-b bg-[#f7f8fc]">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        <span class="rounded-full bg-[#efefef] px-4 py-1.5 text-xs font-semibold text-[#777]">Auto</span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-text-input type="datetime-local" wire:model="solutionForm.resolution_date" class="w-full rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('solutionForm.resolution_date')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->tester_id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">Solution</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <x-text-input type="text" wire:model="solutionForm.resolution_description" class="w-full rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('solutionForm.resolution_description')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="solutionForm.resolved_by_user_id" :options="$users" placeholder="Select user" class="rounded-full border-0 bg-[#efefef] shadow-none" />
                        <x-input-error :messages="$errors->get('solutionForm.resolved_by_user_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide bg-[#CFF3DA] text-[#2E9F57]">
                            SOLVED
                        </span>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-6 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse

                @for ($i = 0; $i < $emptyRows; $i++)
                    <tr class="border-b last:border-0">
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    <td class="px-5 py-3 text-sm whitespace-nowrap align-top">&nbsp;</td>
                    </tr>
                    @endfor
            </tbody>
        </table>
    </div>

    <div class="mt-auto pt-4">
        {{ $this->rows->links() }}
    </div>
</div>