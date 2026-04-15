<div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h3 class="text-xl font-bold text-[#2C3E50]">Add New Tester</h3>
        <button
            class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition text-sm"

            type="button">
            Edit Tester
        </button>

        <button
            class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition text-sm"
            wire:click="$dispatch('switchTab', { tab: 'all' })"
            type="button">
            Back to List
        </button>
    </div>



</div>