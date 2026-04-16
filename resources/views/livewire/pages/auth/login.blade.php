<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-2 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-2 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        @if (Route::has('password.request'))
            <div class="text-center mt-8">
                <a class="underline text-sm text-dark-grey hover:text-primary rounded-md focus:outline-none" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        @endif

        <div class="flex justify-center mt-4">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-primary shadow-sm focus:ring-highlight bg-grey" name="remember">
                <span class="ms-2 text-sm text-black">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex justify-center mt-8 mb-4">
            <x-primary-button class="w-full sm:w-1/2 !rounded-full sm:!rounded-md py-3 sm:py-2">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-4">
            <p class="text-sm text-dark-grey">
                {{ __("Don't have an account?") }} 
                <a href="{{ route('register') }}" class="text-dark-grey underline hover:text-black transition" wire:navigate>
                    {{ __('Register here') }}
                </a>
            </p>
        </div>
    </form>
</div>