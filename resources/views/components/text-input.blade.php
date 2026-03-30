@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-light-grey border-none text-black focus:border-highlight focus:ring-highlight rounded-[30px] shadow-sm']) }}>