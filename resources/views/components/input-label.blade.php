@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-xs text-black ml-4']) }}>
    {{ $value ?? $slot }}
</label>
