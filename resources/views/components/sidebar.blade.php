@props([
'title' => 'Sidebar',
'items' => [],
])

<div class="w-60 min-h-screen p-0 bg-white">
    <h2 class="text-xl font-bold px-2 py-4 flex flex-col items-center"
        style="background-color: #E18BA1; color: #111;">
        {{ $title }}
    </h2>
    @if (trim($slot))
    {{ $slot }}
    @elseif (!empty($items))
    <ul>
        @foreach ($items as $item)
        <li class="mb-2 {{ isset($item['tab']) && $activeTab === $item['tab'] ? 'bg-red-100 font-bold' : '' }}" style="border-radius: 0;"> <button
                wire:click="..."
                class="block w-full px-4 py-3 font-normal text-base rounded-none flex flex-col items-center"
                style="color: #111; text-align:left; background: transparent; border: none;">
                {{ $item['label'] }}
            </button>
            <div class="w-4/5 mx-auto border-b border-gray-300"></div>
        </li>
        @endforeach
    </ul>
    @endif
</div>