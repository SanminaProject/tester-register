@props([
    'title' => 'Sidebar', // 默认标题
    'items' => [],        // 菜单项数组
])

<div class="w-60 min-h-screen p-0 bg-white">
    <h2 class="text-xl font-bold px-2 py-4 flex flex-col items-center" 
        style="background-color: #E18BA1; color: #111;">
        {{ $title }}
    </h2>
    <ul>
        @foreach ($items as $item)
            <li class="mb-2">
                <a href="{{ $item['href'] ?? '#' }}"
                   class="block px-4 py-3 font-normal text-base rounded-none hover:bg-red-50 flex flex-col items-center"
                   style="color: #111;">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>