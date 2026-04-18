@props([
'label' => '',
'options' => [],
'placeholder' => 'Select option...',
'valueKey' => 'id',
'labelKey' => 'name',
'error' => null,
])

<div>
    <label class="block text-sm font-medium text-gray-700 text-left">{{ $label }}</label>
    <select {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>
        @if($placeholder !== '')
        <option value="">{{ $placeholder }}</option>
        @endif

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