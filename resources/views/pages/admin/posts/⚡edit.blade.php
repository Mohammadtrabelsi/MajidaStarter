<?php

use App\Models\Post;
use App\Services\CategoryService;
use App\Services\PostService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Edit post')] class extends Component
{
    public Post $post;

    public string $activeLocale = 'en';

    public array $title = [];

    public array $excerpt = [];

    public array $body = [];

    public string $slug = '';

    public string $status = Post::STATUS_DRAFT;

    public ?int $categoryId = null;

    public array $locales = ['en' => 'English', 'ar' => 'العربية'];

    public function mount(Post $post): void
    {
        $this->authorize('manage posts');

        $this->post = $post;
        $this->slug = $post->slug;
        $this->status = $post->status;
        $this->categoryId = $post->category_id;

        foreach (array_keys($this->locales) as $locale) {
            $this->title[$locale] = $post->getTranslation('title', $locale, false) ?? '';
            $this->excerpt[$locale] = $post->getTranslation('excerpt', $locale, false) ?? '';
            $this->body[$locale] = $post->getTranslation('body', $locale, false) ?? '';
        }
    }

    #[Computed]
    public function categories(): array
    {
        return app(CategoryService::class)->options();
    }

    public function save(PostService $posts): void
    {
        $this->authorize('manage posts');

        $this->validate([
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'excerpt.*' => ['nullable', 'string'],
            'body.en' => ['required', 'string'],
            'body.ar' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($this->post->id)],
            'status' => ['required', Rule::in(array_keys(Post::statuses()))],
            'categoryId' => ['nullable', 'exists:categories,id'],
        ]);

        $posts->update($this->post, [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'slug' => $this->slug,
            'status' => $this->status,
            'category_id' => $this->categoryId,
        ]);

        session()->flash('status', 'Post updated successfully.');

        $this->redirect(route('admin.posts.index'), navigate: true);
    }
};
?>

<div style="max-width: 720px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('admin.posts.index') }}" wire:navigate class="text-muted" style="font-size: 13px;">&larr; Back to posts</a>
        <h2 style="margin-top: 8px;">Edit post</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">Update post content and publishing details.</p>
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
                        <x-input-label :for="'title-'.$code" :value="'Title ('.$label.')'" />
                        <x-text-input
                            wire:model="title.{{ $code }}"
                            id="title-{{ $code }}"
                            type="text"
                            :dir="$code === 'ar' ? 'rtl' : 'ltr'"
                            :error="$errors->first('title.'.$code)"
                        />
                        <x-input-error :message="$errors->first('title.'.$code)" />
                    </div>

                    <div class="field" style="margin-bottom: 0;">
                        <x-input-label :for="'excerpt-'.$code" :value="'Excerpt ('.$label.')'" />
                        <textarea
                            wire:model="excerpt.{{ $code }}"
                            id="excerpt-{{ $code }}"
                            rows="2"
                            dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                            class="textarea"
                        ></textarea>
                    </div>

                    <div class="field" style="margin-bottom: 0;">
                        <x-input-label :for="'body-'.$code" :value="'Body ('.$label.')'" />
                        <textarea
                            wire:model="body.{{ $code }}"
                            id="body-{{ $code }}"
                            rows="8"
                            dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                            class="textarea"
                        ></textarea>
                        <x-input-error :message="$errors->first('body.'.$code)" />
                    </div>
                </div>
            @endforeach
        </div>

        <div style="border-top: 1px solid var(--color-divider); padding-top: 20px; display: flex; flex-direction: column; gap: 16px;">
            <div class="field" style="margin-bottom: 0;">
                <x-input-label for="slug" value="Slug" />
                <x-text-input wire:model="slug" id="slug" type="text" :error="$errors->first('slug')" />
                <x-input-error :message="$errors->first('slug')" />
            </div>

            <div class="field" style="margin-bottom: 0;">
                <x-input-label for="categoryId" value="Category" />
                <select wire:model="categoryId" id="categoryId" class="input">
                    <option value="">— None —</option>
                    @foreach ($this->categories as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error :message="$errors->first('categoryId')" />
            </div>

            <div class="field" style="margin-bottom: 0;">
                <x-input-label for="status" value="Status" />
                <select wire:model="status" id="status" class="input">
                    @foreach (\App\Models\Post::statuses() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :message="$errors->first('status')" />
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Save changes</button>
            <a href="{{ route('admin.posts.index') }}" wire:navigate class="btn">Cancel</a>
        </div>
    </form>
</div>
