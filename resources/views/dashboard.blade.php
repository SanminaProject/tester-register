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

    <!-- Mobile Floating Action Button for Scan -->
    <a href="{{ route('scan') }}" 
       class="fixed sm:hidden z-50 bottom-10 right-6 w-[96px] h-[96px] bg-primary text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-red-800 transition active:scale-95 touch-none"
       style="box-shadow: 0 10px 15px -5px rgba(177, 5, 48, 0.4);">
       <div class="relative flex items-center justify-center w-full h-full">
           <svg width="56" height="56" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg" class="absolute pointer-events-none">
               <g clip-path="url(#clip0_337_2532)">
               <path d="M47.2497 54.0005H35.9998V49.5005H47.2497C47.8465 49.5005 48.4188 49.2634 48.8407 48.8415C49.2627 48.4195 49.4997 47.8472 49.4997 47.2505V36.0005H53.9997V47.2505C53.9997 49.0407 53.2886 50.7576 52.0227 52.0234C50.7568 53.2893 49.0399 54.0005 47.2497 54.0005Z" fill="currentColor"/>
               <path d="M4.5 18H0V6.75C0 4.95979 0.711159 3.2429 1.97703 1.97703C3.2429 0.711159 4.95979 0 6.75 0L18 0V4.5H6.75C6.15326 4.5 5.58097 4.73705 5.15901 5.15901C4.73705 5.58097 4.5 6.15326 4.5 6.75V18Z" fill="currentColor"/>
               <path d="M18 54.0005H6.75C4.95979 54.0005 3.2429 53.2893 1.97703 52.0234C0.711159 50.7576 0 49.0407 0 47.2505L0 36.0005H4.5V47.2505C4.5 47.8472 4.73705 48.4195 5.15901 48.8415C5.58097 49.2634 6.15326 49.5005 6.75 49.5005H18V54.0005Z" fill="currentColor"/>
               <path d="M53.9997 18H49.4997V6.75C49.4997 6.15326 49.2627 5.58097 48.8407 5.15901C48.4188 4.73705 47.8465 4.5 47.2497 4.5H35.9998V0H47.2497C49.0399 0 50.7568 0.711159 52.0227 1.97703C53.2886 3.2429 53.9997 4.95979 53.9997 6.75V18Z" fill="currentColor"/>
               </g>
               <defs>
               <clipPath id="clip0_337_2532">
               <rect width="54" height="54" fill="white"/>
               </clipPath>
               </defs>
           </svg>
           <span class="text-[12px] font-extrabold tracking-wide mt-[2px] pointer-events-none z-10" style="font-family: inherit;">SCAN</span>
       </div>
    </a>
</x-app-layout>
