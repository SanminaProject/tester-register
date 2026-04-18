<div class="flex w-full">
    <x-sidebar
        class="{{ $activeTab === 'details' ? 'hidden md:block' : '' }}"
        title="Admin"
        :active-tab="$activeTab"
        :items="[
            ['label' => 'Personnel', 'tab' => 'personnel'],
            ['label' => 'Roles & Access', 'tab' => 'roles'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
        ]" />

    <div class="flex-1  min-w-0 px-6 py-3">
        @if ($activeTab === 'personnel')
        <livewire:pages.admin.personnel-table />
        @elseif ($activeTab === 'roles')
        <livewire:pages.admin.edit-user-roles />
        @elseif ($activeTab === 'logs')
        <livewire:pages.admin.admin-audit-logs />
        @elseif ($activeTab === 'details')
        <livewire:pages.testers.tester-details :testerId="$selectedTesterId" wire:key="tester-details-{{ $selectedTesterId }}" />
        @endif
    </div>
</div>