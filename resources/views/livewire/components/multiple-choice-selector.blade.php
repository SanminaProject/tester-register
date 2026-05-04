<div>
    <select wire:change="addRecipient($event.target.value)"
            class="w-full bg-light-grey border-none text-black focus:border-highlight focus:ring-highlight rounded-[30px] shadow-sm px-5 py-2.5 text-sm transition">
        <option value="">{{ $placeholder }}</option>

        @foreach($users->whereNotIn('id', $selectedIds) as $user)
            <option value="{{ $user->id }}">
                {{ $user->first_name }} {{ $user->last_name }}
            </option>
        @endforeach
    </select>

    <div class="flex flex-wrap gap-2 mt-3">
        @foreach($this->selectedRecipients as $user)
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-secondary text-sm">
                {{ $user->first_name }} {{ $user->last_name }}

                <button
                    type="button"
                    wire:click="removeRecipient({{ $user->id }})"
                    class="ml-2"
                >
                    ×
                </button>
            </span>
        @endforeach
    </div>
</div>