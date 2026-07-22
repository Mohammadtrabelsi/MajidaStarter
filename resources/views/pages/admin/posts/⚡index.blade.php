<?php

use App\Models\Post;
use App\Services\PostService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Posts')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('manage posts');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deletePost(int $postId, PostService $posts): void
    {
        $this->authorize('manage posts');

        $posts->delete(Post::findOrFail($postId));

        session()->flash('status', 'Post deleted successfully.');
    }

    #[Computed]
    public function stats(): array
    {
        return app(PostService::class)->stats();
    }

    #[Computed]
    public function posts()
    {
        return app(PostService::class)->searchPaginated($this->search);
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
            <h2>Posts</h2>
            <p class="text-muted ms-note">{{ __('posts.write_and_publish_content') }}</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" wire:navigate class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M12 5v14M5 12h14"></path></svg>
            {{ __('posts.new_post') }}
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
            ['posts.total_posts', $this->stats['total']],
            ['posts.published', $this->stats['published']],
            ['posts.drafts', $this->stats['drafts']],
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
                        <th>{{ __('posts.title') }}</th>
                        <th>{{ __('posts.category') }}</th>
                        <th>{{ __('posts.author') }}</th>
                        <th>{{ __('posts.status') }}</th>
                        <th class="ms-text-right">{{ __('posts.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->posts as $post)
                        <tr wire:key="post-{{ $post->id }}">
                            <td>
                                <div class="ms-fw-500">{{ $post->title }}</div>
                                <div class="text-muted ms-fs-12">{{ $post->slug }}</div>
                            </td>
                            <td class="text-muted">{{ $post->category?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $post->author?->name ?? '—' }}</td>
                            <td>
                                <span class="tag {{ $post->isPublished() ? 'tag-accent' : 'tag-neutral' }}">{{ \App\Models\Post::statuses()[$post->status] ?? $post->status }}</span>
                            </td>
                            <td class="ms-text-right">
                                <div class="ms-actions-end">
                                    <a
                                        href="{{ route('admin.posts.edit', $post) }}"
                                        wire:navigate
                                        class="btn ms-btn-sm"
                                    >Edit</a>
                                    <button
                                        type="button"
                                        wire:click="deletePost({{ $post->id }})"
                                        wire:confirm="Permanently delete this post? This cannot be undone."
                                        class="btn ms-btn-sm-danger"
                                    >{{ __('posts.delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted ms-empty">{{ __('posts.no_posts_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ms-cell-top">
            {{ $this->posts->links() }}
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/admin/posts/index.css') }}">
</div>
