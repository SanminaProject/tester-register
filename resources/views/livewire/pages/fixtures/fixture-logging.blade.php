<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">{{ $isEdit ? 'Edit Fixture Details' : 'Add New Fixture' }}</h3>

                <x-action-message on="saved" class="me-3">
                    Saved.
                </x-action-message>

                <x-primary-button type="submit">
                    Save
                </x-primary-button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input
                            label="Name"
                            wire:model="form.name"
                        />
                        <x-input-error :messages="$errors->get('form.name')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <x-text-input
                            label="Description"
                            wire:model="form.description"
                        />
                        <x-input-error :messages="$errors->get('form.description')" />
                    </div>

                    <div>
                        <x-select-field
                            label="Manufacturer"
                            wire:model="form.manufacturer"
                            :options="$manufacturers"
                        />
                        <x-input-error :messages="$errors->get('form.manufacturers')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-select-field
                            label="Tester"
                            wire:model="form.tester_id"
                            :options="$testers"
                        />
                        <x-input-error :messages="$errors->get('form.tester_id')" />
                    </div>

                    <div>
                        <x-select-field
                            label="Location"
                            wire:model="form.location_id"
                            :options="$locations"
                        />
                        <x-input-error :messages="$errors->get('form.location_id')" />
                    </div>

                    <div>
                        <x-select-field
                            label="Status"
                            wire:model="form.fixture_status"
                            :options="$statuses"
                        />
                        <x-input-error :messages="$errors->get('form.fixture_status')" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>