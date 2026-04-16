<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-primary border border-transparent rounded-full font-semibold text-sm text-white tracking-wider hover:opacity-90 focus:opacity-0 active:opacity-100 focus:outline-none transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
