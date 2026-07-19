<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    /**
     * CategoryService::searchPaginated
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json(
            $this->categories->searchPaginated(
                $validated['search'] ?? null,
                $validated['per_page'] ?? 10,
            )
        );
    }

    /**
     * CategoryService::options
     */
    public function options(): JsonResponse
    {
        return response()->json($this->categories->options());
    }

    /**
     * CategoryService::stats
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->categories->stats());
    }

    /**
     * CategoryService::create
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'is_active' => ['boolean'],
        ]);

        $category = $this->categories->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? [],
            'slug' => $validated['slug'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($category, 201);
    }

    /**
     * CategoryService::update
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'is_active' => ['boolean'],
        ]);

        $category = $this->categories->update($category, [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? [],
            'slug' => $validated['slug'] ?? null,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);

        return response()->json($category);
    }

    /**
     * CategoryService::delete
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->categories->delete($category);

        return response()->json(null, 204);
    }
}
