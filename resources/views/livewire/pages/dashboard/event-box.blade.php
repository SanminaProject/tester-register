<div class="bg-white overflow-hidden shadow-sm sm:rounded-xl {{ $type }} flex flex-col h-full">
    
    <div class="px-6 pt-4 text-gray-900 flex-1">
        <h3 class="text-base font-semibold mb-1">{{ $title }}</h3>
        <hr class="mt-3 mb-0 border-gray-200">

        <ul class="flex flex-col">
            @forelse($items as $item)
                <li class="px-2 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        
                        <div class="text-xs font-semibold px-2 py-1 rounded flex items-center justify-center {{ $item['type'] === 'issue' ? 'w-16' : 'w-1/6' }} {{ $this->getTypeClasses($item['type']) }}">
                                {{ ucfirst($item['type']) }}
                        </div>

                        <div class="font-medium text-gray-800">
                            {{ $item['tester'] }}
                        </div>

                        <div class="ml-auto text-sm text-gray-500">
                            {{ $item['date']->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-gray-500 px-2 py-2">No data available</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-auto">
        <div class="mx-6 border-t border-gray-100"></div>

        <div class="px-6 pt-3 pb-4 flex justify-end">
            <a href="#" class="text-sm font-medium text-gray-500 hover:text-indigo-900 transition">
                View All &rarr;
            </a>
        </div>     
    </div>

</div>