<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function __construct(private readonly PostService $posts) {}

    /**
     * PostService::searchPaginated
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json(
            $this->posts->searchPaginated(
                $validated['search'] ?? null,
                $validated['per_page'] ?? 10,
            )
        );
    }

    /**
     * PostService::stats
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->posts->stats());
    }

    /**
     * PostService::create
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePost($request);

        $post = $this->posts->create([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? [],
            'body' => $validated['body'],
            'slug' => $validated['slug'] ?? null,
            'status' => $validated['status'],
            'category_id' => $validated['category_id'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($post, 201);
    }

    /**
     * PostService::update
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $this->validatePost($request, $post);

        $post = $this->posts->update($post, [
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? [],
            'body' => $validated['body'],
            'slug' => $validated['slug'] ?? null,
            'status' => $validated['status'],
            'category_id' => $validated['category_id'] ?? null,
        ]);

        return response()->json($post);
    }

    /**
     * PostService::delete
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->posts->delete($post);

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePost(Request $request, ?Post $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'array'],
            'excerpt.*' => ['nullable', 'string'],
            'body' => ['required', 'array'],
            'body.en' => ['required', 'string'],
            'body.ar' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'status' => ['required', Rule::in(array_keys(Post::statuses()))],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);
    }
}
