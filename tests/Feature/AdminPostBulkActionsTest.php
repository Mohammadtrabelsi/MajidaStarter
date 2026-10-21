<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPostBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_bulk_publish_updates_every_selected_post(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Post::factory()->create();
        $second = Post::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.posts.index')
            ->set('selected', [(string) $first->id, (string) $second->id])
            ->set('bulkAction', 'publish')
            ->call('applyBulk');

        $this->assertSame(Post::STATUS_PUBLISHED, $first->fresh()->status);
        $this->assertSame(Post::STATUS_PUBLISHED, $second->fresh()->status);
        $this->assertNotNull($first->fresh()->published_at);
    }

    public function test_trash_and_restore_from_the_list(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($admin)->test('pages::admin.posts.index')->call('deletePost', $post->id);
        $this->assertSoftDeleted($post);

        Livewire::actingAs($admin)->test('pages::admin.posts.index')->call('restorePost', $post->id);
        $this->assertNull($post->fresh()->deleted_at);
    }

    public function test_bulk_publish_skips_posts_the_user_cannot_edit(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo('manage posts');
        $own = Post::factory()->create(['user_id' => $editor->id]);
        $foreign = Post::factory()->create(['user_id' => User::factory()->create()->id]);

        Livewire::actingAs($editor)
            ->test('pages::admin.posts.index')
            ->set('selected', [(string) $own->id, (string) $foreign->id])
            ->set('bulkAction', 'publish')
            ->call('applyBulk');

        $this->assertSame(Post::STATUS_PUBLISHED, $own->fresh()->status);
        $this->assertSame(Post::STATUS_DRAFT, $foreign->fresh()->status);
    }
}
