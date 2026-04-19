<div class="flex">
    <x-sidebar 
        title="Services" 
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Schedules', 'tab' => 'schedules'],
            ['label' => 'Maintenance & Calibration', 'tab' => 'maintenance'],
            ['label' => 'Add New Log', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs']
        ]" 
    />
        
    <div class="flex-1 px-6 py-3">
        @if ($activeTab === 'schedules')
        <livewire:pages.services.service-schedule />
        @else
        <!-- Placeholder for other tabs -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                Work in progress: {{ $activeTab }}
            </div>
        </div>
        @endif
    </div>
</div>
