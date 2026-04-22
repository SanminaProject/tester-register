<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">{{ $isEdit ? 'Edit Issue' : 'Add New Issue' }}</h3>

                <x-action-message on="saved" class="me-3">
                    Saved.
                </x-action-message>

                <x-primary-button type="submit">
                    Save
                </x-primary-button>
            </div>

            @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
                {{ session('message') }}
            </div>
            @endif

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label value="Log ID" />
                        <div class="mt-1 rounded-[30px] bg-light-grey px-4 py-2 text-sm text-gray-700">
                            Auto Generated
                        </div>
                    </div>

                    <div>
                        <x-input-label for="date" value="Date" />
                        <x-text-input id="date" type="date" wire:model="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tester_id" value="Tester ID" />
                        <x-select-field
                            id="tester_id"
                            wire:model="tester_id"
                            :options="$testers"
                            placeholder="Select tester" />
                        <x-input-error :messages="$errors->get('tester_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label value="Type" />
                        <div class="mt-1 rounded-[30px] bg-light-grey px-4 py-2 text-sm text-gray-700 lowercase">
                            {{ $type }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <x-input-label for="problem" value="Description" />
                        <x-text-input id="problem" type="text" wire:model="problem" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('problem')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="created_by_user_id" value="User" />
                        <x-select-field id="created_by_user_id" wire:model="created_by_user_id" :options="$users" placeholder="Select user" />
                        <x-input-error :messages="$errors->get('created_by_user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status_id" value="Status" />
                        <x-testers.dropdown-field
                            id="status_id"
                            wire:model="status_id"
                            :options="$statuses"
                            placeholder="Select status"
                            error="status_id" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>