<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('New post')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('manage posts');
    }
};
?>

<div class="ms-mw-720">
    <div class="ms-mb-24">
        <a href="{{ route('admin.posts.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; {{ __('posts.back_to_posts') }}</a>
        <h2 class="ms-mt-8">{{ __('posts.new_post') }}</h2>
        <p class="text-muted ms-note">{{ __('posts.write_a_new_post') }}</p>
    </div>

    <livewire:admin.posts.form />
</div>
