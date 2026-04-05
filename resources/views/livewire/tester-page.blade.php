<div class="flex">
    <x-sidebar title="Testers">
        <ul>
            @foreach ([
            ['label'=> 'All Testers', 'tab' => 'all'],
            ['label' => 'Add New Tester', 'tab' => 'add'],
            ['label' => 'Audit Logs', 'tab' => 'logs'],
            ['label' => 'Add New Log', 'tab' => 'addlog']
            ] as $item)
            <li>
                <button wire:click="setTab('{{ $item['tab'] }}')"
                    class="block w-full px-4 py-3 font-normal text-base rounded-none text-center hover:bg-red-50 transition {{ $activeTab === $item['tab'] ? 'bg-red-100 font-bold' : '' }}"
                    style="color: #111;">
                    {{ $item['label'] }}
                </button>
                @if (!$loop->last)
                <div class="mx-auto w-4/5 border-b border-gray-300"></div>
                @endif
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