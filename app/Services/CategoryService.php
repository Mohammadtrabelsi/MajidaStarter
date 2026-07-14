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
            ->when($search, fn ($query) => $query->where('slug', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function stats(): array
    {
        return [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
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
