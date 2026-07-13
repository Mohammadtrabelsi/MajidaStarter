<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::guest')] #[Title('Verify email')] class extends Component
{
    public function mount(): void
    {
        if (auth()->user()->hasVerifiedEmail()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function sendVerification(): void
    {
        if (auth()->user()->hasVerifiedEmail()) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        auth()->user()->sendEmailVerificationNotification();

        session()->flash('status', 'verification-link-sent');
    }
};
?>

<div>
    <h1>Verify your email</h1>
    <p class="text-muted" style="margin-bottom: 24px;">
        Thanks for signing up! Before getting started, please confirm your email address by clicking the
        link we just sent to <strong>{{ auth()->user()->email }}</strong>. Didn't receive it? We'll gladly
        send another.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="tag tag-accent" style="display: block; margin-bottom: 16px; padding: 8px 12px;">
            A fresh verification link has been sent to your email address.
        </div>
    @endif

    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <button
            type="button"
            wire:click="sendVerification"
            wire:loading.attr="disabled"
            wire:target="sendVerification"
            class="btn btn-primary"
        >
            Resend verification email
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn">Log out</button>
        </form>
    </div>
</div>
