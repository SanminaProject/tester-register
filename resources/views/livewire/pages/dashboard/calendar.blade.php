<div wire:ignore>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6 text-gray-900">
            <div 
                id="calendar" 
                data-events='@json($events)'
                class="form-control min-h-[400px]">
            </div>
        </div>
    </div>
</div>