<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')] #[Title('Reset password')] class extends Component
{
    public string $token = '';

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        session()->flash('status', __($status));
        $this->redirect(route('login'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reset your password</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose a new password for your account</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <x-input-label for="email" value="Email address" />
            <x-text-input
                wire:model="email"
                id="email"
                type="email"
                autofocus
                autocomplete="username"
                :error="$errors->first('email')"
            />
            <x-input-error :message="$errors->first('email')" />
        </div>

        <div>
            <x-input-label for="password" value="New password" />
            <x-text-input
                wire:model="password"
                id="password"
                type="password"
                autocomplete="new-password"
                placeholder="••••••••"
                :error="$errors->first('password')"
            />
            <x-input-error :message="$errors->first('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm new password" />
            <x-text-input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="••••••••"
            />
        </div>

        <x-primary-button wire:loading.attr="disabled" wire:target="resetPassword">
            <svg wire:loading wire:target="resetPassword" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            Reset password
        </x-primary-button>
    </form>
</div>
