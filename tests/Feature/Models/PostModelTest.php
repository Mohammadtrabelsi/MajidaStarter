<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_post_defaults_to_draft_status(): void
    {
        $this->assertSame(Post::STATUS_DRAFT, (new Post)->status);
    }

    public function test_is_published_reflects_status(): void
    {
        $this->assertTrue((new Post(['status' => Post::STATUS_PUBLISHED]))->isPublished());
        $this->assertFalse((new Post(['status' => Post::STATUS_DRAFT]))->isPublished());
    }

    public function test_statuses_returns_the_label_map(): void
    {
        $this->assertSame([
            Post::STATUS_DRAFT => 'Draft',
            Post::STATUS_PUBLISHED => 'Published',
        ], Post::statuses());
    }

    public function test_published_scope_only_returns_published_posts(): void
    {
        Post::factory()->published()->count(2)->create();
        Post::factory()->create(['status' => Post::STATUS_DRAFT]);

        $published = Post::published()->get();

        $this->assertCount(2, $published);
        $this->assertTrue($published->every(fn (Post $post) => $post->isPublished()));
    }

    public function test_published_at_is_cast_to_a_date(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertInstanceOf(Carbon::class, $post->published_at);
    }
}
