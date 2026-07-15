<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')] #[Title('Forgot password')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate();

        $result = Password::sendResetLink(['email' => $this->email]);

        if ($result === Password::RESET_LINK_SENT) {
            $this->status = __($result);
            $this->reset('email');

            return;
        }

        $this->addError('email', __($result));
    }
};
?>

<div>
    <h1>Forgot your password?</h1>
    <p class="text-muted" style="margin-bottom: 28px;">No worries — we'll send you a link to reset it.</p>

    @if ($status)
        <div class="tag tag-accent" style="display: block; margin-bottom: 20px; padding: 8px 12px;">{{ $status }}</div>
    @endif

    <form wire:submit="sendResetLink">
        <div class="field" style="margin-bottom: 22px;">
            <x-input-label for="email" value="Email" />
            <x-text-input
                wire:model="email"
                id="email"
                type="email"
                autofocus
                autocomplete="username"
                placeholder="you@company.com"
                :error="$errors->first('email')"
            />
            <x-input-error :message="$errors->first('email')" />
        </div>

        <x-primary-button wire:loading.attr="disabled" wire:target="sendResetLink">
            <svg wire:loading wire:target="sendResetLink" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            Email password reset link
        </x-primary-button>
    </form>

    <p class="text-muted" style="text-align: center; font-size: 13px; margin-top: 22px;">
        Remembered your password?
        <a href="{{ route('login') }}" wire:navigate>Back to sign in</a>
    </p>
</div>
