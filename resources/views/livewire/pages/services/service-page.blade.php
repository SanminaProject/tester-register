<div class="flex">
    <x-sidebar 
        title="Services" 
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Schedules', 'tab' => 'schedules'],
            ['label' => 'Maintenance & Calibration', 'tab' => 'maintenance'],
            ['label' => 'Audit Logs', 'tab' => 'logs']
        ]" 
    />
        
    <div class="flex-1 px-6 py-3">
        @if ($activeTab === 'schedules')
        <livewire:pages.services.service-schedule />
        @elseif ($activeTab === 'maintenance')
        <livewire:pages.services.maintenance-settings />
        @elseif ($activeTab === 'logs')
        <div class="w-full pt-4">
            <livewire:components.data-table
                type="tester-audit-logs"
                title="Service Audit Logs"
                searchPlaceholder="Search service audit logs..."
                :showAddButton="false"
                :headers="[
                    'id' => 'ID',
                    'explanation' => 'Action Details',
                    'changed_at' => 'Changed At',
                    'tester_id' => 'Tester ID',
                    'tester.name' => 'Tester Name',
                    'user.name' => 'Modified By',
                    'user.email' => 'Email'
                ]"
            />
        </div>
        @endif
    </div>
</div>
