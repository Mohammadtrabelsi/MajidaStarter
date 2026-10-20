<?php

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Edit category')] class extends Component
{
    public Category $category;

    public function mount(Category $category): void
    {
        $this->authorize('manage categories');

        $this->category = $category;
    }
};
?>

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; Back to categories</a>
        <h2 class="ms-mt-8">{{ __('categories.edit_category') }}</h2>
        <p class="text-muted ms-note">{{ __('categories.update_category_details') }}</p>
    </div>

    <livewire:admin.categories.form :category="$category" />
</div>
