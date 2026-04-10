@props([
    'title' => 'Sidebar',
    'items' => [],
    'activeTab' => null, 
])

<div class="w-1/6 min-h-screen p-0 bg-white">
    <h2 class="text-xl font-bold px-2 py-4 flex flex-col items-center bg-secondary text-black">
        {{ $title }}
    </h2>
    
    @if (trim($slot))
        {{ $slot }}
    @elseif (!empty($items))
        <ul class="flex flex-col">
            @foreach ($items as $item)
            @php
                $isActive = isset($item['tab']) && $activeTab === $item['tab'];
            @endphp
            
            <li class="{{ $isActive ? 'bg-thirdly font-bold' : 'border-b border-gray-100' }}">
                <button
                    wire:click="$set('activeTab', '{{ $item['tab'] }}')"
                    class="block w-full px-4 py-4 font-normal text-base-medium text-canter text-black bg-transparent border-none outline-none cursor-pointer">
                    {{ $item['label'] }}
                </button>
            </li>
            @endforeach
        </ul>
    @endif
</div>