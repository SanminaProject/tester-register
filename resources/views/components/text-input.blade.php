@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-light-grey border-none text-black focus:border-none focus:ring-gray-100 rounded-[30px] shadow-sm']) }}>