@props([
    'label' => '',
    'options' => [],
    'placeholder' => 'Select option',
    'valueKey' => 'id',
    'labelKey' => 'name',
    'multiple' => false,
    'searchable' => false,
    'searchPlaceholder' => 'Type to search',
])

<div>
    <x-input-label :value="$label" />

    @if(! $searchable)
        <select {{ $attributes->merge(['class' => 'mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-gray-400 focus:border-none sm:text-sm']) }}>
            <option value=""> {{ $placeholder }} </option>

            @foreach ($options as $option)
                <option value="{{ is_array($option) ? $option[$valueKey] : $option->$valueKey }}">
                    {{ is_array($option) ? $option[$labelKey] : $option->$labelKey }}
                </option>
            @endforeach
        </select>
    @else
        @php
            $normalizedOptions = collect($options)->map(function ($option) use ($valueKey, $labelKey) {
                if (is_array($option)) {
                    $value = $option[$valueKey] ?? '';
                    $label = $option[$labelKey] ?? '';
                } elseif (is_object($option)) {
                    $value = $option->{$valueKey} ?? '';
                    $label = $option->{$labelKey} ?? '';
                } else {
                    $value = $option;
                    $label = $option;
                }

                return [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ];
            })->values()->all();
        @endphp

        <div
            x-data="{
                open: false,
                query: '',
                selectedValue: '',
                options: @js($normalizedOptions),
                init() {
                    this.selectedValue = String(this.$refs.modelInput.value ?? '');

                    this.$nextTick(() => {
                        this.selectedValue = String(this.$refs.modelInput.value ?? '');
                    });

                    this.$refs.modelInput.addEventListener('input', (e) => {
                        this.selectedValue = String(e.target.value ?? '');
                    });
                },
                filteredOptions() {
                    const query = this.query.trim().toLowerCase();

                    if (!query) {
                        return this.options;
                    }

                    return this.options.filter((option) => {
                        return String(option.value).toLowerCase().includes(query) || String(option.label).toLowerCase().includes(query);
                    });
                },
                selectedLabel() {
                    if (!this.selectedValue) return @js($placeholder);

                    const selected = this.options.find((option) => String(option.value) === String(this.selectedValue));

                    return selected ? selected.label : @js($placeholder);
                },
                setSelected(value, closeDropdown = true) {
                    this.selectedValue = value === null || value === undefined ? '' : String(value);
                    this.$refs.modelInput.value = this.selectedValue;
                    this.$refs.modelInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.modelInput.dispatchEvent(new Event('change', { bubbles: true }));

                    if (closeDropdown) {
                        this.open = false;
                    }
                },
                choose(value) {
                    this.setSelected(value);
                    this.query = '';
                },
                openDropdown() {
                    this.open = true;
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                },
                closeDropdown() {
                    this.open = false;
                    this.query = '';
                }
            }"
            x-init="init()"
            class="relative"
        >
            <input type="hidden" x-ref="modelInput" {{ $attributes->except(['class']) }} />

            <button
                type="button"
                @click="open ? closeDropdown() : openDropdown()"
                class="mt-1 flex w-full items-center justify-between rounded-[30px] bg-light-grey px-5 py-2.5 text-left text-sm text-black shadow-sm transition hover:bg-[#efebeb]"
            >
                <span class="truncate" x-text="selectedLabel()"></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-3 h-4 w-4 flex-shrink-0 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.outside="closeDropdown()"
                class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg bg-[#f7f4f4] shadow-lg ring-1 ring-black/5"
                style="display: none;"
            >
                <div class="border-b border-gray-200 p-2">
                    <input
                        x-ref="searchInput"
                        x-model="query"
                        type="text"
                        class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-highlight focus:ring-highlight"
                        placeholder="{{ $searchPlaceholder }}"
                    />
                </div>

                <div class="max-h-72 overflow-auto px-2 py-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-md px-2 py-2 text-sm font-medium text-gray-700 transition hover:bg-white"
                        @click="choose('')"
                    >
                        <span>-</span>
                    </button>

                    <template x-for="option in filteredOptions()" :key="option.value">
                        <div class="mt-1 flex w-full items-center justify-between rounded-md px-2 py-2 text-sm text-gray-800 transition hover:bg-white">
                            <button
                                type="button"
                                class="flex-1 text-left truncate"
                                @click="choose(option.value)"
                            >
                                <span class="truncate" x-text="option.label"></span>
                                <span class="ml-2 text-xs text-gray-500" x-text="option.value"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="filteredOptions().length === 0" class="px-2 py-3 text-sm text-gray-500">
                        No matching options
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>