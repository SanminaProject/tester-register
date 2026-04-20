<div class="flex min-h-[calc(100vh-8rem)] w-full min-w-0 flex-col rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold pl-8">{{ $this->headerTitle }}</h3>
        <div class="flex items-center gap-3">
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
                @if ($showInlineForm && $mode === 'add_issue')
                <tr class="border-b bg-pink-50">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Auto Generated</span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-text-input type="date" wire:model="issueForm.date" class="w-full" />
                        <x-input-error :messages="$errors->get('issueForm.date')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="issueForm.tester_id" :options="$testers" placeholder="Select tester" />
                        <x-input-error :messages="$errors->get('issueForm.tester_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top lowercase">problem</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <x-text-input type="text" wire:model="issueForm.description" class="w-full" />
                        <x-input-error :messages="$errors->get('issueForm.description')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="issueForm.created_by_user_id" :options="$users" placeholder="Select user" />
                        <x-input-error :messages="$errors->get('issueForm.created_by_user_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-testers.dropdown-field
                            wire:model="issueForm.status_id"
                            :options="$statuses"
                            placeholder="Select status"
                            error="issueForm.status_id" />
                    </td>
                </tr>
                @endif

                @forelse ($this->rows as $row)
                <tr
                    class="border-b hover:bg-thirdly cursor-pointer transition-colors duration-150"
                    wire:click="beginAddSolution({{ $row->id }})">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->tester_id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap lowercase">{{ $row->eventType?->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <div class="max-w-[340px] whitespace-pre-line break-words">{{ $row->description ?? '-' }}</div>
                        <button
                            type="button"
                            wire:click.stop="beginAddSolution({{ $row->id }})"
                            class="mt-2 inline-flex items-center rounded-full bg-[#efefef] px-3 py-1 text-xs font-semibold text-[#6d6d6d] hover:bg-[#e0e0e0]">
                            + Add Solution
                        </button>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                        {{ $row->createdBy?->email ?? ($this->userLabelById[$row->created_by_user_id] ?? '-') }}
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->issueStatusRelation?->name ?? '-' }}</td>
                </tr>

                @if ($showInlineForm && $mode === 'add_solution' && $selectedIssueId === (int) $row->id)
                <tr class="border-b bg-blue-50">
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-text-input type="date" wire:model="solutionForm.resolution_date" class="w-full" />
                        <x-input-error :messages="$errors->get('solutionForm.resolution_date')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $row->tester_id }}</td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap lowercase">solution</td>
                    <td class="px-5 py-3 text-sm text-gray-800 align-top">
                        <x-text-input type="text" wire:model="solutionForm.resolution_description" class="w-full" />
                        <x-input-error :messages="$errors->get('solutionForm.resolution_description')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="solutionForm.resolved_by_user_id" :options="$users" placeholder="Select user" />
                        <x-input-error :messages="$errors->get('solutionForm.resolved_by_user_id')" class="mt-2" />
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap align-top">
                        <x-select-field wire:model="solutionForm.status_id" :options="$statuses" placeholder="Select status" />
                        <x-input-error :messages="$errors->get('solutionForm.status_id')" class="mt-2" />
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-6 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-auto pt-4">
        {{ $this->rows->links() }}
    </div>
</div>