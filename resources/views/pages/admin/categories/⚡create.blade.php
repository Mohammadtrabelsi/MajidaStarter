<?php

use App\Services\CategoryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('New category')] class extends Component
{
    public string $activeLocale = 'en';

    public array $name = [];

    public array $description = [];

    public string $slug = '';

    public bool $isActive = true;

    public array $locales = ['en' => 'English', 'ar' => 'العربية'];

    public function mount(): void
    {
        $this->authorize('manage categories');

        foreach (array_keys($this->locales) as $locale) {
            $this->name[$locale] = '';
            $this->description[$locale] = '';
        }
    }

    public function save(CategoryService $categories): void
    {
        $this->authorize('manage categories');

        $this->validate([
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'isActive' => ['boolean'],
        ]);

        $categories->create([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?: null,
            'is_active' => $this->isActive,
        ]);

        session()->flash('status', 'Category created successfully.');

        $this->redirect(route('admin.categories.index'), navigate: true);
    }
};
?>

<div class="ms-mw-640">
    <div class="ms-mb-24">
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="text-muted ms-fs-13">&larr; Back to categories</a>
        <h2 class="ms-mt-8">{{ __('categories.new_category') }}</h2>
        <p class="text-muted ms-note">{{ __('categories.create_a_category_to_group_related_posts') }}</p>
    </div>

    <form wire:submit="save" class="card ms-stack-24">
        <div>
            <div class="ms-tabs">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="$set('activeLocale', '{{ $code }}')"
                        @class(['ms-locale-tab', 'active' => $activeLocale === $code])
                    >{{ $label }}</button>
                @endforeach
            </div>

            @foreach ($locales as $code => $label)
                <div @class(['ms-locale-panel', 'active' => $activeLocale === $code])>
                    <div class="field ms-mb-0">
                        <x-input-label :for="'name-'.$code" :value="'Name ('.$label.')'" />
                        <x-text-input
                            wire:model="name.{{ $code }}"
                            id="name-{{ $code }}"
                            type="text"
                            :dir="$code === 'ar' ? 'rtl' : 'ltr'"
                            :error="$errors->first('name.'.$code)"
                        />
                        <x-input-error :message="$errors->first('name.'.$code)" />
                    </div>

                    <div class="field ms-mb-0">
                        <x-input-label :for="'description-'.$code" :value="'Description ('.$label.')'" />
                        <textarea
                            wire:model="description.{{ $code }}"
                            id="description-{{ $code }}"
                            rows="3"
                            dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                            class="textarea"
                        ></textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="field ms-section-top">
            <x-input-label for="slug" value="Slug" />
            <x-text-input wire:model="slug" id="slug" type="text" placeholder="Generated from name if left blank" :error="$errors->first('slug')" />
            <x-input-error :message="$errors->first('slug')" />
        </div>

        <label class="ms-option-card">
            <input wire:model="isActive" type="checkbox" class="checkbox ms-mt-2">
            <span>
                <span class="ms-block-strong">{{ __('categories.active') }}</span>
                <span class="text-muted ms-block-fs12">{{ __('categories.inactive_categories_stay_hidden_from_public_listings') }}</span>
            </span>
        </label>

        <div class="ms-row-10">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('categories.create_category') }}</button>
            <a href="{{ route('admin.categories.index') }}" wire:navigate class="btn">{{ __('categories.cancel') }}</a>
        </div>
    </form>
</div>
