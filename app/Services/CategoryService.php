<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function create(array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'] ?? null);

        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'] ?? null, $category);

        $category->fill($data);
        $category->save();

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * @return array<int, string>
     */
    public function options(): array
    {
        return Category::query()
            ->orderBy('slug')
            ->get()
            ->mapWithKeys(fn (Category $category) => [$category->id => $category->name])
            ->all();
    }

    public function searchPaginated(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->withCount('posts')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('slug', 'like', "%{$search}%")
                ->orWhere('name->en', 'like', "%{$search}%")
                ->orWhere('name->ar', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        // Single aggregate query instead of three separate COUNT statements.
        $counts = Category::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when is_active = ? then 1 else 0 end) as active', [true])
            ->selectRaw('sum(case when is_active = ? then 1 else 0 end) as inactive', [false])
            ->toBase()
            ->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'active' => (int) ($counts->active ?? 0),
            'inactive' => (int) ($counts->inactive ?? 0),
        ];
    }

    /**
     * Build a unique slug, falling back to the name when none is supplied.
     */
    protected function uniqueSlug(?string $slug, string|array|null $name, ?Category $ignore = null): string
    {
        $source = $slug ?: (is_array($name) ? ($name['en'] ?? reset($name) ?: '') : (string) $name);
        $base = Str::slug($source) ?: 'category';
        $candidate = $base;
        $suffix = 2;

        while (Category::query()
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
