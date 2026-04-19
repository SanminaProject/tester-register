<div class="flex flex-col w-full rounded-[24px] md:rounded-2xl bg-white px-5 md:px-10 pt-6 md:pt-8 pb-10 md:pb-12 shadow-[0_2px_10px_rgba(0,0,0,0.02)] md:shadow-sm font-sans text-gray-800 relative">
    
    <!-- Add an overlay if tester is not selected, but we will handle it with logic instead -->
    
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-8 border-b border-gray-200 gap-4 md:gap-0">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-extrabold text-black tracking-tight">Maintenance & Calibration Settings</h2>
        </div>
        <button wire:click="toggleEdit" 
                @if(!$selectedTesterId) disabled @endif
                class="w-32 py-2.5 text-[15px] font-medium rounded-full text-white transition-colors self-end md:self-auto disabled:opacity-50 disabled:cursor-not-allowed"
                style="background-color: #C11232;">
            {{ $isEditing ? 'Save' : 'Edit' }}
        </button>
    </div>

    <!-- Tester Info -->
    <div class="flex flex-col gap-y-4 md:gap-y-6 pb-8 border-b border-[#e8e8e8] mb-8">
        <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start relative">
            <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px] pt-1">Search Tester</div>
            <div class="relative w-full max-w-[400px]">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                           class="block w-full text-[14px] md:text-[15px] font-semibold md:font-extrabold border border-gray-300 rounded bg-gray-50 py-2 pk-3 focus:ring-1 focus:ring-primary focus:border-primary" 
                           placeholder="Type Tester ID or Name...">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                
                @if(count($searchResults) > 0)
                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded shadow-lg max-h-60 overflow-y-auto">
                        <ul class="py-1 text-sm text-gray-700">
                            @foreach($searchResults as $result)
                                <li>
                                    <button wire:click="selectTester('{{ $result['id'] }}')" type="button" class="block w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold cursor-pointer">
                                        {{ $result['id'] }} - {{ $result['name'] }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        @if($selectedTesterId)
        <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start mt-2">
            <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">ID</div>
            <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $testerId }}</div>
        </div>
        <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
            <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Name</div>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $testerName }}</div>
                <div class="text-[13px] md:text-[14px] font-medium flex items-center gap-1 cursor-pointer hover:text-black transition-colors" style="color: #8c8c8c;">
                    View Tester Details
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($selectedTesterId)
    <!-- Maintenance Section -->
    <div class="mb-10">
        <h3 class="text-lg md:text-xl font-extrabold text-black mb-6">Maintenance</h3>
        
        <div class="flex flex-col gap-y-4 md:gap-y-6 lg:pl-4">
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Last Maintenance Date</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $lastMaintenanceDate }}</div>
            </div>
            
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Last Maintenance User</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $lastMaintenanceUser }}</div>
            </div>

            <div class="grid grid-cols-[145px_1fr] lg:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center py-1">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Maintenance Period</div>
                <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-4">
                    <div class="w-full max-w-[240px]">
                        @if($isEditing)
                            <select wire:model.live="maintenancePeriodId" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 pl-3 pr-8 focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">Select Period...</option>
                                @foreach($maintenanceOptions as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                                @if($maintenancePeriodId === 'custom')
                                <option value="custom">Custom ({{ $customMaintenanceLabel }})</option>
                                @endif
                            </select>
                        @else
                            <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $maintenancePeriodLabel }}</div>
                        @endif
                    </div>
                    @if($isEditing)
                    <div class="text-[12px] md:text-[13px] text-[#8c8c8c]">
                        *Next Maintenance Date will be updated based on Maintenance Period.
                    </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-[145px_1fr] lg:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center py-1">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Next Maintenance Date</div>
                <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-4">
                    <div class="w-full max-w-[240px]">
                        @if($isEditing)
                            <input type="datetime-local" wire:model.live="nextMaintenanceDate" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary">
                        @else
                            <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $nextMaintenanceDate ? date('j.n.Y H:i', strtotime($nextMaintenanceDate)) : '-' }}</div>
                        @endif
                    </div>
                    <div class="text-[12px] md:text-[13px] text-[#8c8c8c]">
                        *Maintenance Period will be calculated based on Next Maintenance Date.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center pt-2">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Next Maintenance User</div>
                <div class="w-full max-w-[240px]">
                    @if($isEditing)
                        <div class="relative">
                            <input list="maintenance-users-list" wire:model="nextMaintenanceUserId" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Search user...">
                            <datalist id="maintenance-users-list">
                                @foreach($users as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="text-[12px] text-gray-500 mt-1">Select an ID to assign</div>
                    @else
                        <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $nextMaintenanceUser }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="border-t border-[#e8e8e8] mb-8"></div>

    <!-- Calibration Section -->
    <div class="mb-4">
        <h3 class="text-lg md:text-xl font-extrabold text-black mb-6">Calibration</h3>
        
        <div class="flex flex-col gap-y-4 md:gap-y-6 lg:pl-4">
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Last Calibration Date</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $lastCalibrationDate }}</div>
            </div>
            
            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-start">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Last Calibration User</div>
                <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px]">{{ $lastCalibrationUser }}</div>
            </div>

            <div class="grid grid-cols-[145px_1fr] lg:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center py-1">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Calibration Period</div>
                <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-4">
                    <div class="w-full max-w-[240px]">
                        @if($isEditing)
                            <select wire:model.live="calibrationPeriodId" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 pl-3 pr-8 focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">Select Period...</option>
                                @foreach($calibrationOptions as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                                @if($calibrationPeriodId === 'custom')
                                <option value="custom">Custom ({{ $customCalibrationLabel }})</option>
                                @endif
                            </select>
                        @else
                            <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $calibrationPeriodLabel }}</div>
                        @endif
                    </div>
                    @if($isEditing)
                    <div class="text-[12px] md:text-[13px] text-[#8c8c8c]">
                        *Next Calibration Date will be updated based on Calibration Period.
                    </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-[145px_1fr] lg:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center py-1">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Next Calibration Date</div>
                <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-4">
                    <div class="w-full max-w-[240px]">
                        @if($isEditing)
                            <input type="datetime-local" wire:model.live="nextCalibrationDate" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary">
                        @else
                            <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $nextCalibrationDate ? date('j.n.Y H:i', strtotime($nextCalibrationDate)) : '-' }}</div>
                        @endif
                    </div>
                    <div class="text-[12px] md:text-[13px] text-[#8c8c8c]">
                        *Calibration Period will be calculated based on Next Calibration Date.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-[145px_1fr] md:grid-cols-[200px_1fr] gap-x-2 md:gap-x-4 items-center pt-2">
                <div class="text-[#8c8c8c] md:text-gray-600 tracking-wide text-[14px] md:text-[16px]">Next Calibration User</div>
                <div class="w-full max-w-[240px]">
                    @if($isEditing)
                        <div class="relative">
                            <input list="calibration-users-list" wire:model="nextCalibrationUserId" class="block w-full text-[14px] md:text-[15px] border border-gray-300 rounded bg-gray-50 py-2 md:py-2.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Search user...">
                            <datalist id="calibration-users-list">
                                @foreach($users as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="text-[12px] text-gray-500 mt-1">Select an ID to assign</div>
                    @else
                        <div class="text-[#1a1a1a] md:text-black font-semibold md:font-extrabold text-[14px] md:text-[16px] py-1 md:py-2">{{ $nextCalibrationUser }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="flex items-center justify-center py-20 text-[#8c8c8c]">
        Please search and select a tester above to view or modify calibration & maintenance settings.
    </div>
    @endif
</div>
