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
        {{ $attributes->merge(['class' => 'bg-gray-100 rounded-full px-8 py-4 border-none shadow-none w-full text-black focus:ring-2 focus:ring-blue-200 focus:outline-none transition']) }}>

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>