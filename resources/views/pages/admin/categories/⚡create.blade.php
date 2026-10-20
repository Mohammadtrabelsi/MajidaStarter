<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('New category')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('manage categories');
    }
};
?>

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; Back to categories</a>
        <h2 class="ms-mt-8">{{ __('categories.new_category') }}</h2>
        <p class="text-muted ms-note">{{ __('categories.create_a_category_to_group_related_posts') }}</p>
    </div>

    <livewire:admin.categories.form />
</div>
