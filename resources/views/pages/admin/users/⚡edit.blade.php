<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Edit user')] class extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->authorize('manage users');

        $this->user = $user;
    }
};
?>

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.users.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; {{ __('users.back_to_users') }}</a>
        <h2 class="ms-mt-8">{{ __('users.edit') }}</h2>
        <p class="text-muted ms-note">{{ __('users.edit_description') }}</p>
    </div>

    <livewire:admin.users.form :user="$user" />
</div>
