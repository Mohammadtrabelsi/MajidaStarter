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

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-muted ms-fs-13">&larr; {{ __('users.back_to_users') }}</a>
        <h2 class="ms-mt-8">{{ __('users.create') }}</h2>
        <p class="text-muted ms-note">{{ __('users.create_description') }}</p>
    </div>

    <form wire:submit="save" class="card ms-stack-16">
        <div class="field ms-mb-0">
            <x-input-label for="name" value="Full name" />
            <x-text-input wire:model="name" id="name" type="text" autofocus :error="$errors->first('name')" />
            <x-input-error :message="$errors->first('name')" />
        </div>

        <div class="field ms-mb-0">
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="email" id="email" type="email" :error="$errors->first('email')" />
            <x-input-error :message="$errors->first('email')" />
        </div>

        <div class="field ms-mb-0">
            <x-input-label for="password" value="Password" />
            <x-text-input wire:model="password" id="password" type="password" autocomplete="new-password" :error="$errors->first('password')" />
            <x-input-error :message="$errors->first('password')" />
        </div>

        <div class="field ms-mb-0">
            <x-input-label value="Roles" />
            <div class="ms-radio-stack">
                @forelse ($this->availableRoles as $role)
                    <label class="ms-check-label">
                        <input type="checkbox" wire:model="roles" value="{{ $role }}" class="checkbox">
                        {{ Str::headline($role) }}
                    </label>
                @empty
                    <p class="text-muted ms-note">{{ __('users.no_roles') }}</p>
                @endforelse
            </div>
        </div>

        <div class="ms-row-10">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('users.create') }}</button>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn">Cancel</a>
        </div>
    </form>
</div>
