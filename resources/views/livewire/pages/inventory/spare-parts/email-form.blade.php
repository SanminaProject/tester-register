<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">New Message</h3>

                <div class="flex gap-2">
                    <x-secondary-button type="button" wire:click="$dispatch('switchTab', {tab: 'spare-part-details', sparePartId: {{ $sparePart->id }}})">
                        Cancel
                    </x-secondary-button>

                    <x-primary-button type="submit">
                        Send
                    </x-primary-button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">

                <!-- To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <livewire:components.multiple-choice-selector
                        :selectedIds="$form->responsible_user_ids"
                        :users="$users"
                        placeholder="Select recipient"
                    />
                    <x-input-error :messages="$errors->get('form.responsible_user_ids')" />
                    <p class="mt-2 text-sm text-gray-500"> Recipients are pre-selected based on possible users assigned as responsible for this spare part.</p>
                </div>

                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text"
                        wire:model="form.subject"
                        class="w-full border-gray-300 rounded-md shadow-smfocus:outline-none focus:ring-gray-400 focus:border-none" />
                    <x-input-error :messages="$errors->get('form.subject')" />
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea
                        wire:model="form.body"
                        rows="8"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gray-400 focus:border-none"
                    ></textarea>
                    <x-input-error :messages="$errors->get('form.body')" />
                </div>

            </div>
        </form>
    </div>
</div>