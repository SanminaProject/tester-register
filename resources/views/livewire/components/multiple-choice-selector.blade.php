<div>
    <select wire:change="addRecipient($event.target.value)"
            class="w-full border-gray-300 rounded-md shadow-sm">
        <option value="">Select recipient</option>

        @foreach($users->whereNotIn('id', $selectedIds) as $user)
            <option value="{{ $user->id }}">
                {{ $user->first_name }} {{ $user->last_name }}
            </option>
        @endforeach
    </select>

    <div class="flex flex-wrap gap-2 mt-3">
        @foreach($this->selectedRecipients as $user)
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-sm">
                {{ $user->first_name }} {{ $user->last_name }}

                <button
                    type="button"
                    wire:click="removeRecipient({{ $user->id }})"
                    class="ml-2 text-blue-600 hover:text-blue-900"
                >
                    ×
                </button>
            </span>
        @endforeach
    </div>
</div>