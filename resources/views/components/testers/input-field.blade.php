@props([
'label' => '',
'type' => 'text',
'placeholder' => '',
'error' => null,
])

<div>
    <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ $label }}</label>
    <x-text-input
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full px-5 py-2.5 text-sm']) }} />

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>