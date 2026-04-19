<div class="flex flex-col w-full min-w-0 rounded-xl bg-white p-8 lg:p-10 shadow-sm border border-gray-100 gap-2">

    <!-- Top Row wrapper for the calendar layout mimic -->
    <div class="w-full">
        @include('livewire.pages.dashboard.calendar', ['events' => $calendarEvents, 'view' => 'dayGridMonth'])
    </div>

    <!-- Event list section similar to dashboard, but customized slightly based on requested table mock -->
    <div class="flex w-full flex-col mt-8">
        
        <div class="flex items-center gap-6 mb-4">
            <h2 class="text-xl font-bold">Weekly Events</h2>
            <div class="flex items-center gap-4">
                <button wire:click="previousWeek" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="text-gray-900 text-lg font-medium whitespace-nowrap">{{ $dateRangeDisplay }}</span>
                <button wire:click="nextWeek" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="flex-1 w-full overflow-x-auto relative">
            <div wire:loading wire:target="previousWeek, nextWeek" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <table class="w-full whitespace-nowrap text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-gray-600 font-medium">
                    <tr>
                        <th scope="col" class="px-4 py-3 rounded-l-lg">ID</th>
                        <th scope="col" class="px-4 py-3">Date</th>
                        <th scope="col" class="px-4 py-3">Tester ID</th>
                        <th scope="col" class="px-4 py-3">Maintenance / Calibration</th>
                        <th scope="col" class="px-4 py-3">User</th>
                        <th scope="col" class="px-4 py-3 rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($upcomingEvents as $evt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ data_get($evt, 'id') }}</td>
                            <td class="px-4 py-3">{{ data_get($evt, 'date_formatted') }}</td>
                            <td class="px-4 py-3">{{ data_get($evt, 'tester_id') }}</td>
                            <td class="px-4 py-3">
                                @if(data_get($evt, 'maintenance_calibration') === 'Maintenance')
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Maintenance</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Calibration</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ trim(data_get($evt, 'user', '')) ? data_get($evt, 'user') : 'Unassigned' }}</td>
                            <td class="px-4 py-3 min-w-[140px]">
                                @if(str_starts_with(data_get($evt, 'id'), 'E-'))
                                    @if(data_get($evt, 'status'))
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                            {{ data_get($evt, 'status') }}
                                        </span>
                                    @endif
                                @else
                                    <div class="relative">
                                        <select wire:change="updateEventStatus('{{ data_get($evt, 'id') }}', $event.target.value)"
                                            class="block w-full py-1 pl-3 pr-8 text-xs font-medium border-0 rounded-full focus:ring-2 focus:ring-primary appearance-none cursor-pointer capitalize
                                            @if(strtolower(data_get($evt, 'status')) === 'completed') bg-green-100 text-green-800
                                            @elseif(strtolower(data_get($evt, 'status')) === 'in progress') bg-blue-100 text-blue-800
                                            @elseif(strtolower(data_get($evt, 'status')) === 'overdue') bg-red-100 text-red-800
                                            @elseif(strtolower(data_get($evt, 'status')) === 'cancelled') bg-gray-200 text-gray-800
                                            @elseif(strtolower(data_get($evt, 'status')) === 'scheduled') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif
                                            "
                                        >
                                            @foreach($availableStatuses as $statusOption)
                                                <option value="{{ $statusOption }}" @if(strtolower(data_get($evt, 'status')) === strtolower($statusOption)) selected @endif class="bg-white text-gray-900">
                                                    {{ $statusOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Custom dropdown arrow -->
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-current opacity-60">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 bg-gray-50 rounded-lg">
                                No events found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
