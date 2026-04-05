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
        <li class="border-b border-gray-300 last:border-b-0">
            <a href="{{ $item['href'] ?? '#' }}"
                class="block w-full px-4 py-3 font-normal text-base rounded-none text-center hover:bg-red-50 transition"
                style="color: #111;">
                {{ $item['label'] }}
            </a>
        </li>
        @endforeach
    </ul>
    @endif
</div>