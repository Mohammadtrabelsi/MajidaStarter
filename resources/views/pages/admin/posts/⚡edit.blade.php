<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Edit post')] class extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->authorize('manage posts');

        $this->post = $post;
    }
};
?>

<div class="ms-mw-720">
    <div class="ms-mb-24">
        <a href="{{ route('admin.posts.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; {{ __('posts.back_to_posts') }}</a>
        <h2 class="ms-mt-8">{{ __('posts.edit_post') }}</h2>
        <p class="text-muted ms-note">{{ __('posts.update_post_content') }}</p>
    </div>

    <livewire:admin.posts.form :post="$post" />
</div>
