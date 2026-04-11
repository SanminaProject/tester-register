@props([
    'title' => 'Sidebar',
    'items' => [],
    'activeTab' => null, 
])

<div class="w-1/5 min-h-screen p-0 bg-white">
    <h2 class="text-xl font-bold px-2 py-4 flex flex-col items-center bg-secondary text-black">
        {{ $title }}
    </h2>
    
    @if (trim($slot))
        {{ $slot }}
    @elseif (!empty($items))
        <ul class="flex flex-col">
            @foreach ($items as $item)
            @php
                $isActive = (isset($item['tab']) && $activeTab === $item['tab']) || 
                            (isset($item['href']) && request()->fullUrlIs(url($item['href'])));
            @endphp
            
            <li class="{{ $isActive ? 'bg-thirdly font-bold' : 'border-b border-gray-100' }}">
                
                @if(isset($item['href']))
                    <a href="{{ $item['href'] }}" 
                       class="block w-full px-4 py-3 font-normal text-base text-center text-black bg-transparent no-underline">
                        {{ $item['label'] }}
                    </a>
                    
                @else
                    <button
                        wire:click="$set('activeTab', '{{ $item['tab'] }}')"
                        class="block w-full px-4 py-3 font-normal text-base text-center text-black bg-transparent border-none outline-none cursor-pointer">
                        {{ $item['label'] }}
                    </button>
                @endif
                
            </li>
            @endforeach
        </ul>
    @endif
</div>