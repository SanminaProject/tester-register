@props([
    'label' => '',
    'options' => [],
    'placeholder' => 'Select option',
    'valueKey' => 'id',
    'labelKey' => 'name',
    'multiple' => false,
    'model' => null, 
])

<div>
    <x-input-label :value="$label" />

    @if(!$multiple)
        <select
            {{ $attributes->whereStartsWith('wire:model') }}
            {{ $attributes->merge([
                'class' => 'mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
            ]) }}
        >
            <option value="">{{ $placeholder }}</option>

            @foreach ($options as $option)
                @php
                    $value = is_array($option) ? $option[$valueKey] : $option->$valueKey;
                    $labelText = is_array($option) ? $option[$labelKey] : $option->$labelKey;
                @endphp

                <option value="{{ $value }}">{{ $labelText }}</option>
            @endforeach
        </select>

    @else
        <div class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
            @forelse ($options as $option)
                @php
                    $value = is_array($option) ? $option[$valueKey] : $option->$valueKey;
                    $labelText = is_array($option) ? $option[$labelKey] : $option->$labelKey;
                @endphp

                <label
                    wire:key="option-{{ md5((string) $value) }}"
                    class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 hover:bg-gray-50"
                >
                    <input
                        type="checkbox"
                        value="{{ $value }}"
                        wire:model="{{ $model }}"
                        class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary"
                    >
                    
                    <span class="text-sm text-gray-700 break-words">
                        {{ $labelText }}
                    </span>
                </label>

            @empty
                <div class="px-2 py-1.5 text-xs text-gray-400">
                    No selectable values
                </div>
            @endforelse
        </div>
    @endif
</div>