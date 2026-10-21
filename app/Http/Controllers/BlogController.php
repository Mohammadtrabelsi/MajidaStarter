<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Public reader-facing blog: a listing of published posts and a detail page.
 */
class BlogController extends Controller
{
    public function __construct(private readonly PostService $posts) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString() ?: null;

        return view('blog', [
            'posts' => $this->posts->publishedPaginated($search),
            'search' => $search,
        ]);
    }

    public function show(string $slug): View
    {
        $post = $this->posts->findPublishedBySlug($slug) ?? abort(404);

        return view('blog-post', ['post' => $post]);
    }
}
