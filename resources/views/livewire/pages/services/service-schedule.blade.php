<div class="flex flex-col w-full min-w-0 rounded-xl bg-white p-8 lg:p-10 shadow-sm border border-gray-100 gap-2">

    <!-- Top Row wrapper for the calendar layout mimic -->
    <div class="w-full">
        @include('livewire.pages.dashboard.calendar', ['events' => $calendarEvents, 'view' => 'dayGridMonth'])
    </div>

    <!-- Event list section similar to dashboard, but customized slightly based on requested table mock -->
    <div class="flex w-full flex-col mt-8">
        
        <h2 class="text-xl font-bold mb-4">Event List</h2>
        
        <div class="flex-1 w-full overflow-x-auto">
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
                            <td class="px-4 py-3">
                                @if(data_get($evt, 'status'))
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                    @if(strtolower(data_get($evt, 'status')) === 'completed') bg-green-100 text-green-800
                                    @elseif(strtolower(data_get($evt, 'status')) === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif
                                    ">
                                        {{ data_get($evt, 'status') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">None</span>
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
