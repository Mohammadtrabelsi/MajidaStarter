<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('New user')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('manage users');
    }
};
?>

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-muted ms-fs-13">&larr; {{ __('users.back_to_users') }}</a>
        <h2 class="ms-mt-8">{{ __('users.create') }}</h2>
        <p class="text-muted ms-note">{{ __('users.create_description') }}</p>
    </div>

    <livewire:admin.users.form />
</div>
