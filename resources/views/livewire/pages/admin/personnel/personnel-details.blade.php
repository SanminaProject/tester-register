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

        <x-primary-button type="button" class="w-32" wire:click="enableEdit">
            Edit
        </x-primary-button>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-y-10">
        
        <!-- 2. Main Info Block -->
        <div class="flex flex-col gap-y-3.5 pl-12 w-full max-w-4xl">
            @php
            $rows = [
                'ID' => $user->id,
                'First Name' => $user->first_name,
                'Last Name' => $user->last_name,
                'Role' => $user->role_names,
                'Email' => $user->email,
                'Phone' => $user->phone,
                'Responsibilities' => $user->responsibilities,
                'Tester Names' => $user->tester_names,
                'Qualifications Certifications' => $user->qualifications_certifications,
            ];
            @endphp
            
            @foreach($rows as $label => $value)
            <div class="grid grid-cols-[200px_1fr] gap-x-4 items-start">
                <div class="text-dark-grey tracking-wide text-[16px]">{{ $label }}</div>
                <div class="text-black font-extrabold text-[16px] whitespace-pre-line leading-relaxed">{{ $value ?? '-' }}</div>
            </div>
            @endforeach
        </div>

        @if($editing)
        <div class="mt-8 pl-12">
            <x-testers.dropdown-field
                label="Role"
                wire:model="selectedRoleName"
                :options="$roles"
                valueKey="name"
                labelKey="name"
                class="w-64"
            />

            <div class="mt-8 flex items-center gap-6">
                <x-primary-button wire:click="updatePersonnelRole">
                    Save
                </x-primary-button>

                <button type="button" wire:click="removePersonnelRole" class="text-red-600 underline font-medium bg-transparent p-0 hover:text-red-800">
                    Remove Role
                </button>
            </div>
        </div>
        @endif

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
