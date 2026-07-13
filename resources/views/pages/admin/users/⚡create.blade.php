<?php

use App\Services\UserService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('New user')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public array $roles = [];

    public function mount(): void
    {
        $this->authorize('manage users');
    }

    #[Computed]
    public function availableRoles(): array
    {
        return app(UserService::class)->roleNames();
    }

    public function save(UserService $users): void
    {
        $this->authorize('manage users');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($this->availableRoles)],
        ]);

        $users->create($validated, $this->roles);

        session()->flash('status', 'User created successfully.');

        $this->redirect(route('admin.dashboard'), navigate: true);
    }
};
?>

<div style="max-width: 640px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-muted" style="font-size: 13px;">&larr; Back to users</a>
        <h2 style="margin-top: 8px;">New user</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">Create an account and assign roles.</p>
    </div>

    <form wire:submit="save" class="card" style="display: flex; flex-direction: column; gap: 16px;">
        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="name" value="Full name" />
            <x-text-input wire:model="name" id="name" type="text" autofocus :error="$errors->first('name')" />
            <x-input-error :message="$errors->first('name')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="email" id="email" type="email" :error="$errors->first('email')" />
            <x-input-error :message="$errors->first('email')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label for="password" value="Password" />
            <x-text-input wire:model="password" id="password" type="password" autocomplete="new-password" :error="$errors->first('password')" />
            <x-input-error :message="$errors->first('password')" />
        </div>

        <div class="field" style="margin-bottom: 0;">
            <x-input-label value="Roles" />
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 4px;">
                @forelse ($this->availableRoles as $role)
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" wire:model="roles" value="{{ $role }}" class="checkbox">
                        {{ Str::headline($role) }}
                    </label>
                @empty
                    <p class="text-muted" style="font-size: 13px; margin: 0;">No roles defined.</p>
                @endforelse
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Create user</button>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn">Cancel</a>
        </div>
    </form>
</div>
