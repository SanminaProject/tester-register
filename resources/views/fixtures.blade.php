<x-app-layout>
    <div class="flex">

    <!-- To be fixed later: Not sure if we should use tabs or links for the sidebar.  -->
    <!-- In tester-page, we used tabs in sidebar -->
    <!-- For now, using links here to avoid Livewire state management issues. -->

        <x-sidebar
            title="Fixtures"
            :items="[
                ['label' => 'All Fixtures', 'href' => url('/fixtures')],
                ['label' => 'Add New Fixture', 'href' => url('/fixtures/create')],
                ['label' => 'Audit Logs', 'href' => url('/fixtures/logs')],
                ['label' => 'Add New Log', 'href' => url('/fixtures/logs/create')]
            ]" 
        />
        
        <div class="flex-1 p-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">All Fixtures</h3>
                    <p>This is the fixture List component.</p>
                </div>
            </div>
        </div>
        
    </div>
</x-app-layout>