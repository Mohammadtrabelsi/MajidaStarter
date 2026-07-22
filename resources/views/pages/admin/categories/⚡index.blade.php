<?php

use App\Models\Category;
use App\Services\CategoryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Categories')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('manage categories');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteCategory(int $categoryId, CategoryService $categories): void
    {
        $this->authorize('manage categories');

        $categories->delete(Category::findOrFail($categoryId));

        session()->flash('status', 'Category deleted successfully.');
    }

    #[Computed]
    public function stats(): array
    {
        return app(CategoryService::class)->stats();
    }

    #[Computed]
    public function categories()
    {
        return app(CategoryService::class)->searchPaginated($this->search);
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<div>
    <div class="ms-page-head">
        <div>
            <h2>{{ __('categories.categories') }}</h2>
            <p class="text-muted ms-note">{{ __('categories.organize_your_posts_into_categories') }}</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" wire:navigate class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M12 5v14M5 12h14"></path></svg>
            {{ __('categories.new_category') }}
        </a>
    </div>

    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="tag tag-accent ms-notice"
        >{{ session('status') }}</div>
    @endif

    <div class="stat-cards">
        @foreach ([
            ['categories.total_categories', $this->stats['total']],
            ['categories.active', $this->stats['active']],
            ['categories.inactive', $this->stats['inactive']],
        ] as [$label, $value])
            <div class="card">
                <div class="card-kicker">{{ __($label) }}</div>
                <div class="stat-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="ms-toolbar">
        <div class="ms-search-field">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" class="ms-input-icon"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input ms-pl-32" placeholder="Search by slug…">
        </div>
    </div>

    <div class="card ms-p-0">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('categories.name') }}</th>
                        <th>{{ __('categories.slug') }}</th>
                        <th>{{ __('categories.posts') }}</th>
                        <th>{{ __('categories.status') }}</th>
                        <th class="ms-text-right">{{ __('categories.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="ms-fw-500">{{ $category->name }}</td>
                            <td class="text-muted">{{ $category->slug }}</td>
                            <td class="text-muted">{{ $category->posts_count }}</td>
                            <td>
                                <span class="tag {{ $category->is_active ? 'tag-accent' : 'tag-neutral' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="ms-text-right">
                                <div class="ms-actions-end">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        wire:navigate
                                        class="btn ms-btn-sm"
                                    >{{ __('categories.edit') }}</a>
                                    <button
                                        type="button"
                                        wire:click="deleteCategory({{ $category->id }})"
                                        wire:confirm="Permanently delete this category? This cannot be undone."
                                        class="btn ms-btn-sm-danger"
                                    >{{ __('categories.delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted ms-empty">{{ __('categories.no_categories_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ms-cell-top">
            {{ $this->categories->links() }}
        </div>
    </div>

</div>
