@props([
'label' => '',
'options' => [],
'placeholder' => 'Select option...',
'valueKey' => 'id',
'labelKey' => 'name',
'error' => null,
'manageOptions' => false,
'allowCreate' => false,
'createMethod' => null,
 'deleteMethod' => null,
])

@php
$optionItems = is_iterable($options) ? $options : [];
$isAdmin = auth()->check() && auth()->user()->hasRole('Admin');
$enableCreateMode = $manageOptions && $allowCreate && $isAdmin && filled($createMethod);
$displayPlaceholder = filled($placeholder) ? $placeholder : '-';
@endphp

<div>
    <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ $label }}</label>

    @if(! $enableCreateMode)
        <select {{ $attributes->merge(['class' => 'w-full bg-light-grey border-none text-black focus:border-highlight focus:ring-highlight rounded-[30px] shadow-sm px-5 py-2.5 text-sm transition']) }}>
            <option value="">-</option>

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
    @else
        <div
            x-data="{
                open: false,
                creating: false,
                newOptionValue: '',
                selectedValue: '',
                options: @js(collect($optionItems)->map(function ($option) use ($valueKey, $labelKey) {
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

                    return ['value' => $optionValue, 'label' => $optionLabel];
                })->values()->all()),
                init() {
                    this.selectedValue = String(this.$refs.modelInput.value ?? '');
                },
                onOptionCreated(event) {
                    const detail = event?.detail ?? {};
                    if (detail.createMethod && detail.createMethod !== @js($createMethod)) {
                        return;
                    }

                    const id = String(detail.optionId ?? '');
                    const label = String(detail.optionLabel ?? '');

                    if (id === '') {
                        this.cancelCreate();
                        return;
                    }

                    const exists = this.options.some((option) => String(option.value) === id);
                    if (!exists) {
                        this.options.push({ value: id, label });
                    }

                    // Keep dropdown list open; only fold the add-new panel.
                    this.setSelected(id, false);
                    this.cancelCreate();
                },
                onOptionDeleted(event) {
                    const detail = event?.detail ?? {};
                    if (detail.deleteMethod && detail.deleteMethod !== @js($deleteMethod)) {
                        return;
                    }

                    const id = String(detail.optionId ?? '');
                    if (id === '') {
                        return;
                    }

                    this.options = this.options.filter((option) => String(option.value) !== id);

                    if (String(this.selectedValue) === id) {
                        this.choose('');
                    }
                },
                onDeleteFailed(event) {
                    const detail = event?.detail ?? {};
                    if (detail.deleteMethod && detail.deleteMethod !== @js($deleteMethod)) {
                        return;
                    }

                    if (detail.message) {
                        alert(detail.message);
                    }
                },
                selectedLabel() {
                    const selected = this.options.find((option) => String(option.value) === String(this.selectedValue));
                    return selected ? selected.label : @js($displayPlaceholder);
                },
                setSelected(value, closeList = true) {
                    this.selectedValue = value === null || value === undefined ? '' : String(value);
                    this.$refs.modelInput.value = this.selectedValue;
                    
                    // Use Livewire's direct property update to ensure wire:model syncs
                    const inputName = this.$refs.modelInput.getAttribute('wire:model') || 
                                     this.$refs.modelInput.getAttribute('name');
                    if (inputName && typeof $wire !== 'undefined') {
                        $wire.set(inputName, this.selectedValue);
                    } else {
                        // Fallback: dispatch input event
                        this.$refs.modelInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    if (closeList) {
                        this.open = false;
                    }
                },
                choose(value) {
                    this.setSelected(value, true);
                    this.creating = false;
                    this.newOptionValue = '';
                },
                startCreate() {
                    this.creating = true;
                    this.newOptionValue = '';
                    this.open = true;
                },
                cancelCreate() {
                    this.creating = false;
                    this.newOptionValue = '';
                }
            }"
            x-init="init()"
            class="relative"
            x-on:dropdown-option-created.window="onOptionCreated($event)"
            x-on:dropdown-option-deleted.window="onOptionDeleted($event)"
            x-on:dropdown-option-delete-failed.window="onDeleteFailed($event)"
        >
            <input type="hidden" x-ref="modelInput" {{ $attributes->except(['class']) }} />

            <button
                type="button"
                @click="open = !open"
                class="flex w-full items-center justify-between rounded-[30px] bg-light-grey px-5 py-2.5 text-left text-sm text-black shadow-sm transition hover:bg-[#efebeb]"
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
                @click.outside="open = false; creating = false; newOptionValue = ''"
                class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg bg-[#f7f4f4] shadow-lg ring-1 ring-black/5"
                style="display: none;"
            >
                <div class="max-h-72 overflow-auto px-2 py-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-md px-2 py-2 text-sm font-medium text-gray-700 transition hover:bg-white"
                        @click="choose('')"
                    >
                        <span>-</span>
                    </button>

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

                        <div class="mt-1 flex w-full items-center justify-between rounded-md px-2 py-2 text-sm text-gray-800 transition hover:bg-white">
                            <button
                                type="button"
                                class="flex-1 text-left truncate"
                                @click="choose(@js($optionValue))"
                            >
                                <span class="truncate">{{ $optionLabel }}</span>
                            </button>

                            @if($isAdmin && filled($deleteMethod))
                                <button
                                    type="button"
                                    class="ml-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100"
                                    x-on:click.prevent="if (confirm('Delete this option?')) { $wire.{{ $deleteMethod }}(@js($optionValue)) }"
                                    title="Delete option"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 011.06 0L10 7.88l2.66-2.66a.75.75 0 111.06 1.06L11.06 8.94l2.66 2.66a.75.75 0 11-1.06 1.06L10 10l-2.66 2.66a.75.75 0 11-1.06-1.06L8.94 8.94 6.28 6.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-2 border-t border-gray-200 pt-2">
                        <button
                            type="button"
                            x-show="!creating"
                            class="flex w-full items-center justify-center gap-2 rounded-md px-2 py-2 text-sm font-semibold text-gray-600 transition hover:bg-white"
                            @click="startCreate()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            add new option
                        </button>

                        <div x-show="creating" x-transition class="mt-2 space-y-2">
                            <input
                                type="text"
                                x-model="newOptionValue"
                                class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-black focus:border-secondary focus:ring-secondary"
                                placeholder="Enter new option"
                            />

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex-1 rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#8A0028]"
                                    x-on:click.prevent="$wire.{{ $createMethod }}(newOptionValue)"
                                    x-bind:disabled="newOptionValue.trim() === ''"
                                    x-bind:class="newOptionValue.trim() === '' ? 'opacity-50 cursor-not-allowed' : ''"
                                >
                                    Save
                                </button>

                                <button
                                    type="button"
                                    class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300"
                                    @click="cancelCreate()"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($error)
    <x-input-error :messages="$errors->get($error)" class="mt-1" />
    @endif
</div>