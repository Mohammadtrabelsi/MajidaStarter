<?php

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public ?Category $category = null;

    public string $activeLocale = 'en';

    public array $name = [];

    public array $description = [];

    public string $slug = '';

    public bool $isActive = true;

    public array $locales = ['en' => 'English', 'ar' => 'العربية'];

    public function mount(?Category $category = null): void
    {
        $this->authorize('manage categories');

        $this->category = $category;
        $this->slug = $category?->slug ?? '';
        $this->isActive = $category?->is_active ?? true;

        foreach (array_keys($this->locales) as $locale) {
            $this->name[$locale] = $category?->getTranslation('name', $locale, false) ?? '';
            $this->description[$locale] = $category?->getTranslation('description', $locale, false) ?? '';
        }
    }

    public function save(CategoryService $categories): void
    {
        $this->authorize('manage categories');

        $this->validate([
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string'],
            'slug' => $this->slugRules(),
            'isActive' => ['boolean'],
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->category) {
            $categories->update($this->category, $data);
            $message = 'Category updated successfully.';
        } else {
            $categories->create($data);
            $message = 'Category created successfully.';
        }

        session()->flash('status', $message);

        $this->redirect(route('admin.categories.index'), navigate: true);
    }

    /**
     * An existing category must keep a slug; a new one may leave it blank to
     * have it generated from the name.
     */
    protected function slugRules(): array
    {
        $unique = Rule::unique('categories', 'slug');

        if ($this->category) {
            $unique->ignore($this->category);
        }

        return [$this->category ? 'required' : 'nullable', 'string', 'max:255', $unique];
    }
};
?>

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
        <x-text-input wire:model="slug" id="slug" type="text" placeholder="{{ __('categories.slug_placeholder') }}" :error="$errors->first('slug')" />
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
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
            {{ $category ? __('categories.save_changes') : __('categories.create_category') }}
        </button>
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="btn">{{ __('categories.cancel') }}</a>
    </div>
</form>
