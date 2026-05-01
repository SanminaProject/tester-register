@props([
'label' => '',
'options' => [],
'placeholder' => 'Select option...',
'valueKey' => 'id',
'labelKey' => 'name',
'error' => null,
])

<div>
    <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ $label }}</label>
    <select {{ $attributes->merge(['class' => 'w-full bg-light-grey border-none text-black focus:border-highlight focus:ring-highlight rounded-[30px] shadow-sm px-5 py-2.5 text-sm transition']) }}>
        <option value="">-</option>

        @php
        $optionItems = is_iterable($options) ? $options : [];
        @endphp

        @foreach($optionItems as $option)
        @php
        if (is_array($option)) {
        $optionValue = $option[$valueKey] ?? '';
        $optionLabel = $option[$labelKey] ?? '';
        } elseif (is_object($option)) {
        $optionValue = $option->{$valueKey} ?? '';
        $optionLabel = $option->{$labelKey} ?? '';
        } else {
        $optionValue = $option;
        $optionLabel = $option;
        }
        @endphp

        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>