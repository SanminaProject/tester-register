<x-app-layout>
    <div class="flex">
        <x-sidebar
            title="Fixtures"
            :items="[
            ['label'=> 'All Fixtures', 'href' => '#'],
            ['label' => 'Add New Fixture', 'href' => '#'],
            ['label' => 'Audit Logs', 'href' => '#'],
            ['label' => 'Add New Log', 'href' => '#']
            ]" />
        <div class="flex-1 p-8">
            {{-- Fixture List Card component --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    This is the fixture List component.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>