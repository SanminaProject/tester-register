<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <h1 class="text-xl font-bold mb-4">{{ __('Roles & Access') }}</h1>

        <div class="space-y-6">
            <div>
                <label for="user" class="block text-sm font-medium text-gray-700">{{ __('Select User') }}</label>
                <select id="user" wire:model="selectedUserId" wire:change="selectUser" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- {{ __('Select User') }} --</option>
                    @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            @if($selectedUser)
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('User Details') }}</h2>
                        <p class="mt-2 text-sm text-gray-700"><strong>{{ __('Name:') }}</strong> {{ $selectedUser->name }}</p>
                        <p class="text-sm text-gray-700"><strong>{{ __('Email:') }}</strong> {{ $selectedUser->email }}</p>
                        <p class="text-sm text-gray-700"><strong>{{ __('Current Role:') }}</strong> {{ $selectedUser->roles->pluck('name')->join(', ') ?: __('(none)') }}</p>

                        <div class="mt-4">
                            <x-primary-button wire:click="removeUserRole">
                                {{ __('Remove Role') }}
                            </x-primary-button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 mt-4">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('Assign Role') }}</h2>

                        <div class="mt-3">
                            <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
                            <select id="role" wire:model="selectedRoleName" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- {{ __('Select Role') }} --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-primary-button wire:click="updateUserRole">
                                {{ __('Update Role') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm text-gray-600">{{ __('Select a user to view their details and manage roles.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>