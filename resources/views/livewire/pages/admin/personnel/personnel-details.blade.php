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
                'Name' => $user->name,
                'Role' => $user->roles->pluck('name')->join(', '),
                'Email' => $user->email,
                'Phone' => $user->phone,
                'Responsibilities' => $user->responsibilities,
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
            <label class="block text-sm font-semibold mb-2">Role</label>

            <select wire:model="selectedRoleName" class="border rounded px-3 py-2">
                <option value="">Select role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>

            <div class="mt-4 flex gap-2">
                <button wire:click="updatePersonnelRole"
                    class="bg-blue-500 text-white px-4 py-2 rounded">
                    Save
                </button>

                <button wire:click="removePersonnelRole"
                    class="bg-gray-300 px-4 py-2 rounded">
                    Remove Role
                </button>
            </div>
        </div>
        @endif

        @if(auth()->user() && auth()->user()->hasRole('Admin'))
        <div class="mt-16 pl-10 flex justify-start">
            <button 
                type="button" 
                wire:click="deletePersonnel"
                wire:confirm="Are you sure you want to delete this personnel? This action cannot be undone."
                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg font-semibold text-[14px] transition-colors shadow-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Personnel
            </button>
        </div>
        @endif
    </div>
</div>
