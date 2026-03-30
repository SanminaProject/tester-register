<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @livewire('pages.dashboard.event-box', ['title' => 'Active Issues', 'type' => 'issues'])
                    @livewire('pages.dashboard.event-box', ['title' => 'Upcoming Events', 'type' => 'events'])
                </div>
                <div class="mt-6">
                    @livewire('pages.dashboard.calendar')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
