<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TracksUserActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_while_authenticated_stamps_creator_and_updater(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create();

        $this->assertSame($user->id, $category->created_by);
        $this->assertSame($user->id, $category->updated_by);
    }

    public function test_creating_as_guest_leaves_stamps_null(): void
    {
        $category = Category::factory()->create();

        $this->assertNull($category->created_by);
        $this->assertNull($category->updated_by);
    }

    public function test_updating_stamps_updater_with_current_user(): void
    {
        $creator = User::factory()->create();
        $editor = User::factory()->create();

        $this->actingAs($creator);
        $category = Category::factory()->create();

        $this->actingAs($editor);
        $category->update(['slug' => 'edited-by-second-user']);

        $category->refresh();
        $this->assertSame($creator->id, $category->created_by);
        $this->assertSame($editor->id, $category->updated_by);
    }

    public function test_explicit_created_by_is_not_overwritten(): void
    {
        $actor = User::factory()->create();
        $preset = User::factory()->create();
        $this->actingAs($actor);

        $category = Category::factory()->create(['created_by' => $preset->id]);

        $this->assertSame($preset->id, $category->created_by);
    }

    public function test_creator_and_updater_relations_resolve_users(): void
    {
        $creator = User::factory()->create();
        $editor = User::factory()->create();

        $this->actingAs($creator);
        $category = Category::factory()->create();

        $this->actingAs($editor);
        $category->update(['slug' => 'relation-check']);

        $category->refresh();
        $this->assertTrue($category->creator->is($creator));
        $this->assertTrue($category->updater->is($editor));
    }
}
