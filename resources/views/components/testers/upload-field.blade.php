@props([
'label' => 'Upload',
'placeholder' => 'Upload files',
'model' => 'file',
'multiple' => false,
'accept' => '',
])

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="mt-1 flex justify-center px-6 pt-1 pb-1 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition cursor-pointer relative bg-grey">
        <input type="file" wire:model="{{ $model }}" accept="{{ $accept }}" {{ $multiple ? 'multiple' : '' }} class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p class="text-sm text-gray-600">{{ $placeholder }}</p>
        </div>
    </div>
</div>