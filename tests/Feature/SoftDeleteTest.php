<?php

namespace Tests\Feature;

use App\Exceptions\DomainActionException;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\UserService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_deleting_a_post_moves_it_to_the_trash_and_restore_brings_it_back(): void
    {
        $posts = app(PostService::class);
        $post = Post::factory()->published()->create();

        $posts->delete($post);
        $this->assertSoftDeleted($post);
        $this->assertSame(1, $posts->stats()['trashed']);

        $posts->restore(Post::withTrashed()->findOrFail($post->id));
        $this->assertNull(Post::find($post->id)->deleted_at);
    }

    public function test_force_delete_removes_the_row_and_its_cover_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/cover.jpg', 'x');

        $posts = app(PostService::class);
        $post = Post::factory()->create(['cover_image' => 'posts/cover.jpg']);

        $posts->delete($post);
        $posts->forceDelete(Post::withTrashed()->findOrFail($post->id));

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing('posts/cover.jpg');
    }

    public function test_trashed_post_keeps_its_slug_reserved(): void
    {
        $posts = app(PostService::class);
        $first = $posts->create(['title' => ['en' => 'Hello'], 'body' => ['en' => 'x'], 'status' => Post::STATUS_DRAFT]);
        $posts->delete($first);

        $second = $posts->create(['title' => ['en' => 'Hello'], 'body' => ['en' => 'y'], 'status' => Post::STATUS_DRAFT]);

        $this->assertSame('hello-2', $second->slug);
    }

    public function test_trashed_category_is_hidden_from_options_and_keeps_its_slug(): void
    {
        $categories = app(CategoryService::class);
        $category = Category::factory()->create(['slug' => 'news']);

        $categories->delete($category);

        $this->assertSame([], $categories->options());
        $this->assertSame('news-2', $categories->create(['name' => ['en' => 'News'], 'slug' => 'news', 'is_active' => true])->slug);
    }

    public function test_trashed_user_cannot_authenticate_until_restored(): void
    {
        $users = app(UserService::class);
        $user = User::factory()->create(['email' => 'trashed@example.com', 'password' => 'secret-pass']);

        $users->delete(User::factory()->admin()->create(), $user);
        $this->assertNull($users->authenticate('trashed@example.com', 'secret-pass'));

        $users->restore(User::withTrashed()->findOrFail($user->id));
        $this->assertNotNull($users->authenticate('trashed@example.com', 'secret-pass'));
    }

    public function test_admin_cannot_force_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->expectException(DomainActionException::class);

        app(UserService::class)->forceDelete($admin, $admin);
    }
}
