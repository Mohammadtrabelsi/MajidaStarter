<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function payload(): array
    {
        return [
            'title' => ['en' => 'Edited'],
            'body' => ['en' => 'Edited body'],
            'status' => Post::STATUS_DRAFT,
        ];
    }

    public function test_non_admin_editor_cannot_change_someone_elses_post(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo('manage posts');
        $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($editor)->putJson("/api/posts/{$post->id}", $this->payload())->assertForbidden();
        $this->actingAs($editor)->deleteJson("/api/posts/{$post->id}")->assertForbidden();
    }

    public function test_non_admin_editor_can_change_their_own_post(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo('manage posts');
        $post = Post::factory()->create(['user_id' => $editor->id]);

        $this->actingAs($editor)->putJson("/api/posts/{$post->id}", $this->payload())->assertOk();
    }

    public function test_admin_can_change_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($admin)->putJson("/api/posts/{$post->id}", $this->payload())->assertOk();
    }

    public function test_user_without_permission_cannot_restore_posts(): void
    {
        $member = User::factory()->create();
        $post = Post::factory()->create();
        $post->delete();

        $this->actingAs($member)->postJson("/api/posts/{$post->id}/restore")->assertForbidden();
    }
}
