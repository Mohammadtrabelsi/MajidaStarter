<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_access_post_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/posts')->assertForbidden();
    }

    public function test_admin_can_list_posts(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->count(2)->create();

        $this->actingAs($admin)->getJson('/api/posts')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    public function test_admin_can_create_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->postJson('/api/posts', [
            'title' => ['en' => 'Hello World'],
            'body' => ['en' => 'The body of the post.'],
            'status' => Post::STATUS_PUBLISHED,
            'category_id' => $category->id,
        ])->assertCreated();

        $post = Post::first();
        $this->assertSame('hello-world', $post->slug);
        $this->assertSame($admin->id, $post->user_id);
        $this->assertNotNull($post->published_at);
    }

    public function test_creating_a_post_requires_english_title_and_body(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/posts', [
            'status' => Post::STATUS_DRAFT,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title.en', 'body.en']);
    }

    public function test_admin_can_update_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin)->putJson("/api/posts/{$post->id}", [
            'title' => ['en' => 'Updated title'],
            'body' => ['en' => 'Updated body'],
            'slug' => 'updated-title',
            'status' => Post::STATUS_PUBLISHED,
        ])->assertOk();

        $post->refresh();
        $this->assertSame('updated-title', $post->slug);
        $this->assertTrue($post->isPublished());
    }

    public function test_admin_can_delete_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/posts/{$post->id}")
            ->assertNoContent();

        $this->assertModelMissing($post);
    }

    public function test_admin_can_fetch_post_stats(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->published()->create();
        Post::factory()->create();

        $this->actingAs($admin)->getJson('/api/posts/stats')
            ->assertOk()
            ->assertJson(['total' => 2, 'published' => 1, 'drafts' => 1]);
    }
}
