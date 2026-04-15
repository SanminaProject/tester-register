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

    <div class="flex flex-col min-h-[calc(100vh-8rem)] w-full min-w-0 rounded-xl bg-white px-8 pt-6 pb-8 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6 text-left">
                <h4 class="text-lg font-semibold text-gray-700 border-l-4 border-[#2C3E50] pl-3">Basic Information</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tester ID</label>
                        <input type="text" wire:model="tester_id" placeholder="Enter ID..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tester Name</label>
                        <input type="text" wire:model="name" placeholder="Enter Name..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="space-y-6 bg-gray-50 p-6 rounded-xl border border-gray-100 text-left">
                <h4 class="text-lg font-semibold text-gray-700 border-l-4 border-blue-500 pl-3">Advanced Configuration</h4>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <label class="block text-sm font-medium text-blue-800 mb-2">Copy from Existing</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="search_existing_id" placeholder="Search by ID..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <button type="button" wire:click="copyTesterData"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition">
                            Copy
                        </button>
                    </div>
                </div>

                <div id="dropdown-area" class="space-y-4">
                    <p class="text-xs text-gray-400 italic text-center">Dropdowns will be added here in the next step.</p>
                </div>
            </div>
        </div>
    </div>

</div>