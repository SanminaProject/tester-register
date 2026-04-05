<div class="flex">
    <x-sidebar title="Testers">
        <ul>
            @foreach ([
            ['label'=> 'All Testers', 'tab' => 'all'],
            ['label' => 'Add New Tester', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
            ['label' => 'Add New Log', 'tab' => 'addlog']
            ] as $item)
            <li class="mb-2">
                <button
                    wire:click="$set('activeTab', '{{ $item['tab'] }}')"
                    class="block w-full px-4 py-3 font-normal text-base rounded-none flex flex-col items-center {{ $activeTab === $item['tab'] ? 'bg-red-100 font-bold' : '' }}"
                    style="color: #111; text-align:left;">
                    {{ $item['label'] }}
                </button>
                <div class="w-4/5 mx-auto border-b border-gray-300"></div>
            </li>
            @endforeach
        </ul>
    </x-sidebar>
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