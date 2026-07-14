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

<div style="max-width: 640px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="text-muted" style="font-size: 13px;">&larr; Back to categories</a>
        <h2 style="margin-top: 8px;">New category</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">Create a category to group related posts.</p>
    </div>

    <form wire:submit="save" class="card" style="display: flex; flex-direction: column; gap: 24px;">
        <div>
            <div style="display: flex; gap: 2px; border-bottom: 1px solid var(--color-divider); margin-bottom: 18px;">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="$set('activeLocale', '{{ $code }}')"
                        style="padding: 8px 14px; font-size: 13px; font-weight: 500; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-family: inherit; {{ $activeLocale === $code ? 'color: var(--color-accent-800); border-bottom-color: var(--color-accent);' : 'color: rgba(var(--ink),.6);' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            @foreach ($locales as $code => $label)
                <div style="flex-direction: column; gap: 16px; display: {{ $activeLocale === $code ? 'flex' : 'none' }};">
                    <div class="field" style="margin-bottom: 0;">
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

                    <div class="field" style="margin-bottom: 0;">
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

        <div class="field" style="margin-bottom: 0; border-top: 1px solid var(--color-divider); padding-top: 20px;">
            <x-input-label for="slug" value="Slug" />
            <x-text-input wire:model="slug" id="slug" type="text" placeholder="Generated from name if left blank" :error="$errors->first('slug')" />
            <x-input-error :message="$errors->first('slug')" />
        </div>

        <label style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid var(--color-divider); padding: 14px; cursor: pointer;">
            <input wire:model="isActive" type="checkbox" class="checkbox" style="margin-top: 2px;">
            <span>
                <span style="display: block; font-size: 14px; font-weight: 500;">Active</span>
                <span class="text-muted" style="display: block; font-size: 12px;">Inactive categories stay hidden from public listings.</span>
            </span>
        </label>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Create category</button>
            <a href="{{ route('admin.categories.index') }}" wire:navigate class="btn">Cancel</a>
        </div>
    </form>
</div>
