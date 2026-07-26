<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService
{
    public function create(array $data): Post
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title'] ?? null);
        $data = $this->syncPublishedAt($data);

        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title'] ?? null, $post);
        $data = $this->syncPublishedAt($data, $post);

        $post->fill($data);
        $post->save();

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function searchPaginated(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with(['category', 'author'])
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('slug', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")
                ->orWhere('title->ar', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        // Single aggregate query instead of three separate COUNT statements.
        $counts = Post::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as published', [Post::STATUS_PUBLISHED])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as drafts', [Post::STATUS_DRAFT])
            ->toBase()
            ->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'published' => (int) ($counts->published ?? 0),
            'drafts' => (int) ($counts->drafts ?? 0),
        ];
    }

    /**
     * Ensure a post has a publish timestamp when published, and clears it otherwise.
     */
    protected function syncPublishedAt(array $data, ?Post $post = null): array
    {
        if (! array_key_exists('status', $data)) {
            return $data;
        }

        if ($data['status'] === Post::STATUS_PUBLISHED) {
            $data['published_at'] = $post->published_at ?? now();
        } else {
            $data['published_at'] = null;
        }

        return $data;
    }

    /**
     * Build a unique slug, falling back to the title when none is supplied.
     */
    protected function uniqueSlug(?string $slug, string|array|null $title, ?Post $ignore = null): string
    {
        $source = $slug ?: (is_array($title) ? ($title['en'] ?? reset($title) ?: '') : (string) $title);
        $base = Str::slug($source) ?: 'post';
        $candidate = $base;
        $suffix = 2;

        while (Post::query()
            ->where('slug', $candidate)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
