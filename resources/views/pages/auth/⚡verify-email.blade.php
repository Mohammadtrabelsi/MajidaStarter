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
    <h1>{{ __('verify-email.verify_your_email') }}</h1>
    <p class="text-muted" style="margin-bottom: 24px;">
        {{ __('verify-email.thanks_for_signing_up') }} {{ __('verify-email.before_getting_started') }} <strong>{{ auth()->user()->email }}</strong>. {{ __('verify-email.did_not_receive') }} {{ __('verify-email.will_gladly_send') }}
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="tag tag-accent" style="display: block; margin-bottom: 16px; padding: 8px 12px;">
            {{ __('verify-email.verification_link_sent') }}
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
            {{ __('verify-email.resend_verification_email') }}
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn">{{ __('verify-email.log_out') }}</button>
        </form>
    </div>
</div>
