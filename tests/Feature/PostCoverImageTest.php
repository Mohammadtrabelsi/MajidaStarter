<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostCoverImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
    }

    public function test_admin_can_upload_a_cover_when_creating_a_post(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.posts.create')
            ->set('title.en', 'With Cover')
            ->set('body.en', 'Body text')
            ->set('status', Post::STATUS_PUBLISHED)
            ->set('cover', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::firstOrFail();

        $this->assertNotNull($post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->assertSame(Storage::disk('public')->url($post->cover_image), $post->coverImageUrl());
    }

    public function test_rejects_files_that_are_not_images(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::admin.posts.create')
            ->set('title.en', 'Bad Cover')
            ->set('body.en', 'Body text')
            ->set('cover', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['cover']);
    }

    public function test_replacing_a_cover_removes_the_old_file(): void
    {
        $posts = app(PostService::class);
        Storage::disk('public')->put('posts/old.jpg', 'old');
        $post = Post::factory()->create(['cover_image' => 'posts/old.jpg']);

        $posts->update($post, ['title' => ['en' => 'Same'], 'cover_image' => 'posts/new.jpg']);

        Storage::disk('public')->assertMissing('posts/old.jpg');
        $this->assertSame('posts/new.jpg', $post->fresh()->cover_image);
    }
}
