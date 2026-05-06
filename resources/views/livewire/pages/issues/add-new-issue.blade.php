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
                        <div class="relative w-full">
                            <div class="relative w-full">
                                <input
                                    id="tester_id"
                                    type="text"
                                    wire:model.live.debounce.300ms="searchQuery"
                                    class="w-full pl-10 pr-4 py-2 bg-[#dddddd] rounded-full focus:outline-none focus:ring-2 focus:ring-pink-200 border-0 shadow-none text-sm"
                                    placeholder="Search tester ID..."
                                    style="box-shadow:none;"
                                >
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#2C3E50]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>

                            @if(count($searchResults) > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded shadow-lg max-h-60 overflow-y-auto">
                                    <ul class="py-1 text-sm text-gray-700">
                                        @foreach($searchResults as $result)
                                            <li>
                                                <button wire:click="selectTester('{{ $result['id'] }}')" type="button" class="block w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold cursor-pointer">
                                                    {{ $result['id'] }} - {{ $result['name'] }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
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
                        <textarea id="problem" wire:model="problem" rows="4" class="mt-1 block w-full min-h-[120px] resize-y rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <x-input-error :messages="$errors->get('problem')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="created_by_user_id" value="User" />
                        <x-select-field id="created_by_user_id" wire:model="created_by_user_id" :options="$users" placeholder="Select user" />
                        <x-input-error :messages="$errors->get('created_by_user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status_id" value="Status" />
                        <div class="mt-1">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide bg-[#FFD8DE] text-[#FF4A5A]">
                                ACTIVE
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>