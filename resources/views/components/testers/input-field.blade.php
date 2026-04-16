@props([
'label' => '',
'type' => 'text',
'placeholder' => '',
'error' => null,
])

<div>
    <label class="block text-sm font-medium text-gray-700 text-left">{{ $label }}</label>
    <input
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>