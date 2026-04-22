<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-3xl font-bold text-black">Add Solution</h3>
                <x-primary-button type="submit">Save</x-primary-button>
            </div>

            @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
                {{ session('message') }}
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-t border-gray-200">
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
                        <tr class="border-b">
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->id }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->date?->format('d.m.Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->tester_id }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $this->issueTypeLabel }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800">
                                <div class="whitespace-pre-line">{{ $issue->description }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $this->issueUserLabel }}</td>
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

                        <tr class="border-b">
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->id }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                                <x-text-input type="date" wire:model="resolution_date" class="w-full" />
                                <x-input-error :messages="$errors->get('resolution_date')" class="mt-2" />
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $issue->tester_id }}</td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">Solution</td>
                            <td class="px-5 py-3 text-sm text-gray-800">
                                <x-text-input type="text" wire:model="resolution_description" class="w-full" />
                                <x-input-error :messages="$errors->get('resolution_description')" class="mt-2" />
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                                <x-select-field wire:model="resolved_by_user_id" :options="$users" placeholder="Select user" />
                                <x-input-error :messages="$errors->get('resolved_by_user_id')" class="mt-2" />
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-800 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide bg-[#CFF3DA] text-[#2E9F57]">
                                    SOLVED
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>