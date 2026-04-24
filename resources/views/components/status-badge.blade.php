@props(['status'])

@php
    $status = strtolower((string) $status);

    $classes = match ($status) {
        'active', 'reorder' => 'bg-[#FFD8DE] text-[#FF4A5A]',
        'solved', 'in stock' => 'bg-[#CFF3DA] text-[#2E9F57]',
        default => 'bg-gray-200 text-gray-700',
    };
@endphp

<span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $classes }}">
    {{ strtoupper($status ?: '-') }}
</span>