<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $type }}">
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-4">{{ $title }}</h3>

        <ul class="space-y-2">
            @forelse($items as $item)
                <li class="border rounded-xl p-3 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        
                        <div class="font-medium text-gray-800">
                            {{ $item['tester'] }}
                        </div>

                        <div class="text-sm px-2 py-1 rounded {{ $this->typeClasses($item['type']) }}">
                            {{ ucfirst($item['type']) }}
                        </div>

                        <div class="ml-auto text-sm text-gray-500">
                            {{ $item['date']->format('Y-m-d H:i') }}
                        </div>

                    </div>
                </li>
            @empty
                <li class="text-gray-500">No data available</li>
            @endforelse
        </ul>
    </div>
</div>