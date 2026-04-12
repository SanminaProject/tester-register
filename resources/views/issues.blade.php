<x-app-layout>
    <div class="flex">
        <x-sidebar
            title="Issues"
            :items="[
            ['label'=> 'Active Issues', 'href' => '#'],
            ['label' => 'Add New Issue', 'href' => '#'],
            ['label' => 'Issue History', 'href' => '#'],
            ]" />
        <div class="flex-1 p-8">
            {{-- Issues List Card component --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    This is the issues List component.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>