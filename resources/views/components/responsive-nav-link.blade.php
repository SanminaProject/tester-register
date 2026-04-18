@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-primary bg-thirdly/40 focus:outline-none focus:text-primary focus:bg-thirdly focus:border-primary transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-primary hover:bg-thirdly/20 hover:border-primary/30 focus:outline-none focus:text-primary focus:bg-thirdly/20 focus:border-primary/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
