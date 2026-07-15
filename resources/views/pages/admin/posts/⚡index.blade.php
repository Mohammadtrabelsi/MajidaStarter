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
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
        <div>
            <h2>Posts</h2>
            <p class="text-muted" style="font-size: 13px; margin: 0;">Write and publish content across your site.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" wire:navigate class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M12 5v14M5 12h14"></path></svg>
            New post
        </a>
    </div>

    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="tag tag-accent"
            style="display: block; margin-bottom: 16px; padding: 8px 12px;"
        >{{ session('status') }}</div>
    @endif

    <div class="stat-cards">
        @foreach ([
            ['Total posts', $this->stats['total']],
            ['Published', $this->stats['published']],
            ['Drafts', $this->stats['drafts']],
        ] as [$label, $value])
            <div class="card">
                <div class="card-kicker">{{ $label }}</div>
                <div class="stat-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div style="display: flex; align-items: center; gap: 12px; margin: 24px 0 16px; flex-wrap: wrap;">
        <div style="position: relative; flex: 1; min-width: 220px; max-width: 320px;">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input" placeholder="Search by slug…" style="padding-left: 32px;">
        </div>
    </div>

    <div class="card" style="padding: 0;">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->posts as $post)
                        <tr wire:key="post-{{ $post->id }}">
                            <td>
                                <div style="font-weight: 500;">{{ $post->title }}</div>
                                <div class="text-muted" style="font-size: 12px;">{{ $post->slug }}</div>
                            </td>
                            <td class="text-muted">{{ $post->category?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $post->author?->name ?? '—' }}</td>
                            <td>
                                <span class="tag {{ $post->isPublished() ? 'tag-accent' : 'tag-neutral' }}">{{ \App\Models\Post::statuses()[$post->status] ?? $post->status }}</span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                    <a
                                        href="{{ route('admin.posts.edit', $post) }}"
                                        wire:navigate
                                        class="btn"
                                        style="padding: 6px 12px; font-size: 12px;"
                                    >Edit</a>
                                    <button
                                        type="button"
                                        wire:click="deletePost({{ $post->id }})"
                                        wire:confirm="Permanently delete this post? This cannot be undone."
                                        class="btn"
                                        style="padding: 6px 12px; font-size: 12px; border-color: var(--color-accent-700); color: var(--color-accent-700);"
                                    >Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted" style="padding: 28px; text-align: center;">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 12px 16px; border-top: 1px solid var(--color-divider);">
            {{ $this->posts->links() }}
        </div>
    </div>

    <style>
        .stat-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        @media (max-width: 900px) { .stat-cards { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .stat-cards { grid-template-columns: 1fr; } }
    </style>
</div>
