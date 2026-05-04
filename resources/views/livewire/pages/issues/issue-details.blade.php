<div class="flex flex-col min-h-[calc(100vh-8rem)] bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="$dispatch('switchTab', { tab: 'all' })"
                class="flex h-8 w-8 items-center justify-center rounded hover:bg-gray-100 text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-xl font-extrabold text-black">Issue Details</h2>
        </div>

        @if(auth()->user() && !auth()->user()->hasRole('Guest'))
        <x-primary-button type="button" wire:click="editIssue" class="w-32 justify-center">Edit</x-primary-button>
        @endif
    </div>

    @if (session()->has('message'))
    <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('message') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Date</p>
            <p class="font-semibold text-gray-900">{{ $issue->date?->format('Y-m-d H:i') ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Test ID</p>
            <p class="font-semibold text-gray-900">{{ $issue->tester_id }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Problem</p>
            <p class="font-semibold text-gray-900 whitespace-pre-line">{{ $issue->description }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Solution</p>
            <p class="font-semibold text-gray-900 whitespace-pre-line">{{ $issue->resolution_description ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">User</p>
            <p class="font-semibold text-gray-900">{{ $issue->createdBy?->full_name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Status</p>
            @php
            $statusName = strtolower((string) ($issue->issueStatusRelation?->name ?? ''));
            $isSolved = $statusName === 'solved';
            $isActive = $statusName === 'active';
            @endphp
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $isSolved ? 'bg-[#CFF3DA] text-[#2E9F57]' : ($isActive ? 'bg-[#FFD8DE] text-[#FF4A5A]' : 'bg-gray-200 text-gray-700') }}">
                {{ strtoupper($issue->issueStatusRelation?->name ?? '-') }}
            </span>
        </div>
    </div>

    @if(auth()->user() && auth()->user()->hasRole('Admin'))
    <div class="mt-auto pt-8 flex justify-end">
        <x-danger-button type="button" wire:click="deleteIssue" class="w-32 justify-center">
            Delete
        </x-danger-button>
    </div>
    @endif
</div>