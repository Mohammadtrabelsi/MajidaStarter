<?php

use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Profile')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $delete_password = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(UserService $users): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $users->updateProfile($user, $validated);

        $this->dispatch('profile-updated');
    }

    public function updatePassword(UserService $users): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $users->updatePassword(auth()->user(), $validated['password']);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    public function resendVerification(): void
    {
        auth()->user()->sendEmailVerificationNotification();

        $this->dispatch('verification-sent');
    }

    public function deleteAccount(UserService $users): void
    {
        $this->validate([
            'delete_password' => ['required', 'string', 'current_password'],
        ]);

        $users->deleteOwnAccount(auth()->user());

        $this->redirect('/', navigate: true);
    }
};
?>

<div style="max-width: 720px;">
    <div style="margin-bottom: 24px;">
        <h2>{{ __('profile.profile') }}</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">{{ __('profile.manage_account_info') }}</p>
    </div>

    {{-- Profile information --}}
    <form wire:submit="updateProfile" class="card" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h3 style="font-size: 18px;">Account information</h3>
            <p class="text-muted" style="font-size: 13px; margin: 0;">{{ __('profile.update_name_email') }}</p>
        </div>

        <div
            x-data="{ show: false }"
            x-on:profile-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            style="display: none;"
            class="tag tag-accent"
        >
            <span style="display: block; padding: 8px 12px;">{{ __('profile.profile_updated') }}</span>
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="name" value="Full name" />
            <x-text-input wire:model="name" id="name" type="text" autocomplete="name" :error="$errors->first('name')" />
            <x-input-error :message="$errors->first('name')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="email" id="email" type="email" autocomplete="username" :error="$errors->first('email')" />
            <x-input-error :message="$errors->first('email')" />

            @if (! auth()->user()->hasVerifiedEmail())
                <p class="text-muted" style="font-size: 12px; margin-top: 8px;">
                    Your email address is unverified.
                    <button type="button" wire:click="resendVerification" class="btn-ghost" style="background: none; border: none; padding: 0; cursor: pointer; font: inherit; font-size: 12px; color: var(--color-accent);">
                        Resend verification email
                    </button>
                    <span
                        x-data="{ show: false }"
                        x-on:verification-sent.window="show = true; setTimeout(() => show = false, 3000)"
                        x-show="show"
                        style="display: none; color: var(--color-accent-700);"
                    >— a new link has been sent.</span>
                </p>
            @endif
        </div>

        <div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updateProfile">Save</button>
        </div>
    </form>

    {{-- Update password --}}
    <form wire:submit="updatePassword" class="card" style="margin-top: 16px; display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h3 style="font-size: 18px;">Update password</h3>
            <p class="text-muted" style="font-size: 13px; margin: 0;">Use a long, random password to keep your account secure.</p>
        </div>

        <div
            x-data="{ show: false }"
            x-on:password-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            style="display: none;"
            class="tag tag-accent"
        >
            <span style="display: block; padding: 8px 12px;">{{ __('profile.password_updated') }}</span>
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="current_password" value="Current password" />
            <x-text-input wire:model="current_password" id="current_password" type="password" autocomplete="current-password" :error="$errors->first('current_password')" />
            <x-input-error :message="$errors->first('current_password')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="password" value="New password" />
            <x-text-input wire:model="password" id="password" type="password" autocomplete="new-password" :error="$errors->first('password')" />
            <x-input-error :message="$errors->first('password')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="password_confirmation" value="Confirm new password" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" />
        </div>

        <div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updatePassword">Update password</button>
        </div>
    </form>

    {{-- Delete account --}}
    <div class="card" style="margin-top: 16px; border-color: var(--color-accent-700); display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h3 style="font-size: 18px;">{{ __('profile.delete_account') }}</h3>
            <p class="text-muted" style="font-size: 13px; margin: 0;">
                {{ __('profile.delete_account_description') }}
            </p>
        </div>

        <form wire:submit="deleteAccount" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="field" style="margin-bottom: 0; max-width: 320px;">
                <x-input-label for="delete_password" value="Password" />
                <x-text-input wire:model="delete_password" id="delete_password" type="password" autocomplete="current-password" :error="$errors->first('delete_password')" />
                <x-input-error :message="$errors->first('delete_password')" />
            </div>

            <div>
                <button
                    type="submit"
                    class="btn"
                    style="border-color: var(--color-accent-700); color: var(--color-accent-700);"
                    wire:confirm="Are you sure you want to permanently delete your account?"
                    wire:loading.attr="disabled"
                    wire:target="deleteAccount"
                >
                    {{ __('profile.delete_account') }}
                </button>
            </div>
        </form>
    </div>
</div>
