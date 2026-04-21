@props([
'label' => '',
'rows' => 3,
'placeholder' => '',
'error' => null,
])

<div>
    <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ $label }}</label>
    <textarea
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full bg-light-grey border-none text-black focus:border-highlight focus:ring-highlight rounded-[24px] shadow-sm px-5 py-3 text-sm transition resize-y']) }}></textarea>

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>