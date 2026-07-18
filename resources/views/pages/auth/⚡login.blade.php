<?php

use App\Services\UserService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')] #[Title('Log in')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        if (! app(UserService::class)->attempt($this->email, $this->password, $this->remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirect(auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard'), navigate: true);
    }
};
?>

<div>
    <h1>Welcome back</h1>
    <p class="text-muted" style="margin-bottom: 28px;">{{ __('login.sign_in_to_continue') }}</p>

    @if (session('status'))
        <div class="tag tag-accent" style="display: block; margin-bottom: 16px; padding: 8px 12px;">{{ session('status') }}</div>
    @endif

    <form wire:submit="login">
        <div class="field">
            <x-input-label for="email" value="Email" />
            <x-text-input
                wire:model="email"
                id="email"
                type="email"
                placeholder="you@company.com"
                autofocus
                autocomplete="username"
                :error="$errors->first('email')"
            />
            <x-input-error :message="$errors->first('email')" />
        </div>

        <div class="field" style="margin-bottom: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <label for="password" class="ds-label" style="margin-bottom: 0;">{{ __('login.password') }}</label>
                <a href="{{ route('password.request') }}" wire:navigate style="font-size: 12px;">{{ __('login.forgot_password') }}</a>
            </div>
            <x-text-input
                wire:model="password"
                id="password"
                type="password"
                placeholder="••••••••"
                autocomplete="current-password"
                style="margin-top: 5px;"
                :error="$errors->first('password')"
            />
            <x-input-error :message="$errors->first('password')" />
        </div>

        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 22px; cursor: pointer;">
            <input wire:model="remember" type="checkbox" class="checkbox"> {{ __('login.remember_me') }}
        </label>

        <x-primary-button wire:loading.attr="disabled" wire:target="login">
            <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            {{ __('login.sign_in') }}
        </x-primary-button>
    </form>

    <p class="text-muted" style="text-align: center; font-size: 13px; margin-top: 22px;">
        {{ __('login.no_account') }}
        <a href="{{ route('register') }}" wire:navigate>{{ __('login.create_one') }}</a>
    </p>
</div>
