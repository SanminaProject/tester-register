@props([
    'label' => '',
    'options' => [],
    'placeholder' => 'Select option',
    'valueKey' => 'id',
    'labelKey' => 'name'
])

<div>
    <x-input-label :value="$label" />

    <select {{ $attributes->merge(['class' => 'mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm']) }}>
        
        <option value=""> {{ $placeholder }} </option>

        @foreach ($options as $option)
            <option value="{{ is_array($option) ? $option[$valueKey] : $option->$valueKey }}">
                {{ is_array($option) ? $option[$labelKey] : $option->$labelKey }}
            </option>
        @endforeach

    </select>
</div>