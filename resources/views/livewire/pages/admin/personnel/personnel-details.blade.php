<div class="flex flex-col w-full min-h-[calc(100vh-8rem)] rounded-2xl bg-white px-10 pt-8 pb-12 shadow-sm font-sans text-gray-800">
    <!-- 1. Header (Title & Edit Button) -->
    <div class="flex items-center justify-between pb-6 mb-8 border-b border-gray-200">
        <div class="flex items-center gap-4">
            <!-- Back Arrow -->
            <button 
                type="button" 
                wire:click="$dispatch('switchTab', { tab: 'personnel' })" 
                class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 transition-colors text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black tracking-tight">Personnel Details</h2>
        </div>

        @if(auth()->user() && !auth()->user()->hasRole('Guest'))
            @if($editing)
                <x-primary-button type="button" class="w-32" wire:click="savePersonnelDetails">
                    Save
                </x-primary-button>
            @else
                <x-primary-button type="button" class="w-32" wire:click="enableEdit">
                    Edit
                </x-primary-button>
            @endif
        @endif
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-y-10">
        
        <!-- 2. Main Info Block -->
        <div class="flex flex-col gap-y-3.5 pl-12 w-full max-w-4xl">
            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">ID</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->id ?? '-' }}</div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">First Name</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->first_name ?? '-' }}</div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Last Name</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->last_name ?? '-' }}</div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Role</div>
                <div>
                    @if($editing)
                        <x-testers.dropdown-field
                            wire:model="selectedRoleName"
                            :options="$roles"
                            :manageOptions="true"
                            :allowCreate="true"
                            createMethod="createRoleOption"
                            deleteMethod="deleteRoleOption"
                            valueKey="name"
                            labelKey="name"
                            class="w-64"
                        />
                    @else
                        <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->role_names ?? '-' }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Email</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->email ?? '-' }}</div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Phone</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->phone ?? '-' }}</div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Qualifications Certifications</div>
                <div>
                    @if($editing)
                        <textarea
                            wire:model.defer="qualificationsCertifications"
                            rows="3"
                            class="w-full max-w-2xl rounded-lg border border-gray-300 px-3 py-2 text-[15px] font-medium text-gray-900 focus:border-primary focus:ring-primary"
                        ></textarea>
                    @else
                        <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->qualifications_certifications ?? '-' }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Additional Info</div>
                <div>
                    @if($editing)
                        <textarea
                            wire:model.defer="additionalInfo"
                            rows="3"
                            class="w-full max-w-2xl rounded-lg border border-gray-300 px-3 py-2 text-[15px] font-medium text-gray-900 focus:border-primary focus:ring-primary"
                        ></textarea>
                    @else
                        <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->responsibilities ?? '-' }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">Tester Names</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $user->tester_names ?? '-' }}</div>
            </div>
        </div>

        @if(auth()->user() && auth()->user()->hasRole('Admin'))
        <div class="mt-auto pt-8 flex justify-end">
            <button 
                type="button" 
                wire:click="deletePersonnel"
                wire:confirm="Are you sure you want to delete this personnel? This action cannot be undone."
                class="flex h-10 w-32 items-center justify-center gap-2 rounded-lg bg-red-50 px-5 py-2.5 text-[14px] font-semibold text-red-600 shadow-sm transition-colors cursor-pointer hover:bg-red-600 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>
        @endif
    </div>
</div>
