<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_lists_only_published_posts(): void
    {
        Post::factory()->published()->create(['title' => ['en' => 'Visible Story'], 'slug' => 'visible-story']);
        Post::factory()->create(['title' => ['en' => 'Draft Story'], 'slug' => 'draft-story']);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Visible Story')
            ->assertDontSee('Draft Story');
    }

    public function test_blog_search_matches_titles(): void
    {
        Post::factory()->published()->create(['title' => ['en' => 'Alpha Release'], 'slug' => 'alpha']);
        Post::factory()->published()->create(['title' => ['en' => 'Beta Notes'], 'slug' => 'beta']);

        $this->get('/blog?q=Alpha')
            ->assertOk()
            ->assertSee('Alpha Release')
            ->assertDontSee('Beta Notes');
    }

    public function test_published_post_page_renders(): void
    {
        Post::factory()->published()->create([
            'title' => ['en' => 'Hello There'],
            'body' => ['en' => 'First line'],
            'slug' => 'hello-there',
        ]);

        $this->get('/blog/hello-there')
            ->assertOk()
            ->assertSee('Hello There')
            ->assertSee('First line');
    }

    public function test_draft_post_page_is_not_found(): void
    {
        Post::factory()->create(['slug' => 'secret-draft']);

        $this->get('/blog/secret-draft')->assertNotFound();
    }

    public function test_trashed_post_page_is_not_found(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'gone-post']);
        $post->delete();

        $this->get('/blog/gone-post')->assertNotFound();
    }
}
