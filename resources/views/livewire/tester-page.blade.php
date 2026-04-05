<div class="flex">

    <x-sidebar
        title="Testers"
        :items="[
            ['label'=> 'All Testers', 'tab' => 'all'],
            ['label' => 'Add New Tester', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
            ['label' => 'Add New Log', 'tab' => 'addlog']
        ]" />

    <div class="flex-1 p-8">
        @if ($activeTab === 'all')
        <livewire:all-testers />
        @elseif ($activeTab === 'add')
        <livewire:add-new-tester />
        @elseif ($activeTab === 'logs')
        <livewire:audit-logs />
        @elseif ($activeTab === 'addlog')
        <livewire:add-new-log />
        @endif
    </div>
</div>