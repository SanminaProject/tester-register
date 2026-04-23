<div class="bg-white overflow-hidden shadow-sm rounded-xl {{ $type }} flex flex-col h-full border border-gray-100 sm:border-0">
    <div class="px-4 sm:px-6 pt-3 sm:pt-4 pb-2 sm:pb-0 text-gray-900 flex-1">
        <!-- Unified Title -->
        <h3 class="text-[15px] sm:text-base font-semibold mb-0 sm:mb-1">{{ $title }}</h3>
        <hr class="mt-2 sm:mt-3 mb-1 sm:mb-0 border-gray-100 sm:border-gray-200">

        <ul class="flex flex-col">
            @forelse($items as $item)
            <li class="py-3 sm:px-2 sm:py-2 border-b border-gray-50 sm:border-gray-100 last:border-b-0 hover:bg-gray-50 transition">
                <div class="flex items-center gap-2 sm:gap-4">

                    <div class="text-[10px] sm:text-xs font-bold sm:font-semibold px-1 sm:px-2 py-0.5 sm:py-1 rounded flex items-center justify-center flex-shrink-0 w-24 sm:w-[100px] {{ $this->getTypeClasses($item['type']) }}">
                        {{ ucfirst($item['type']) }}
                    </div>

                    <div class="font-medium text-[13px] sm:text-base text-gray-800 truncate max-w-[120px] sm:max-w-none">
                        {{ $item['tester'] }}
                    </div>

                    <div class="ml-auto text-[11px] sm:text-sm text-gray-500 whitespace-nowrap flex-shrink-0">
                        <span class="block sm:hidden">{{ $item['date']->format('m-d H:i') }}</span>
                        <span class="hidden sm:block">{{ $item['date']->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </li>
            @empty
            <li class="text-gray-500 py-3 sm:px-2 sm:py-2 text-[13px] sm:text-base">No data available</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-auto hidden sm:block">
        <div class="mx-4 sm:mx-6 border-t border-gray-100"></div>

        <div class="px-4 sm:px-6 pt-3 pb-4 flex justify-end">
            <a href="{{ $type === 'events' ? route('services') : route('issues') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-900 transition">
                View All &rarr;
            </a>
        </div>
    </div>

</div>