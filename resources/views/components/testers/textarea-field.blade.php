@props([
'label' => '',
'rows' => 3,
'placeholder' => '',
'error' => null,
])

<div>
    <label class="block text-sm font-medium text-gray-700 text-left">{{ $label }}</label>
    <textarea
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'bg-grey rounded-full px-8 py-4 border-none shadow-none w-full text-black focus:ring-2 focus:ring-blue-200 focus:outline-none transition']) }}></textarea>

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>