<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form wire:submit.prevent="save">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Add New Fixture</h3>

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
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <x-text-input
                            label="Description"
                            wire:model="form.description"
                        />
                    </div>

                    <div>
                        <x-input-label for="manufacturer" value="Manufacturer" />
                        <x-text-input
                            label="Manufacturer"
                            wire:model="form.manufacturer"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="tester_id" value="Tester ID" />
                        <x-text-input
                            label="Tester ID"
                            type="number"
                            wire:model="form.tester_id"
                        />
                    </div>

                    <div>
                        <x-input-label for="location_id" value="Location ID" />
                        <x-text-input
                            label="Location ID"
                            type="number"
                            wire:model="form.location_id"
                        />
                    </div>

                    <div>
                        <x-input-label for="fixture_status" value="Fixture Status" />
                        <x-text-input
                            label="Status ID"
                            type="number"
                            wire:model="form.fixture_status"
                        />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>