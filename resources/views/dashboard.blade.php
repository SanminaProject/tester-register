<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- The pt-* class right here determines the gap between the navbar and the very first card on the screen. (Currently changed from pt-4 to pt-2 for mobile) -->
    <div class="pt-2 pb-12 sm:pt-4 sm:pb-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 sm:block">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-2.5">
                    @livewire('pages.dashboard.event-box', ['title' => 'Active Issues', 'type' => 'issues'])
                    @livewire('pages.dashboard.event-box', ['title' => 'Upcoming Events', 'type' => 'events'])
                </div>
                <div class="mt-0 sm:mt-4">
                    @livewire('pages.dashboard.calendar')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
