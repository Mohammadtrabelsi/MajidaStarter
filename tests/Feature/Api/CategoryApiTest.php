<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_access_category_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/categories')->assertForbidden();
    }

    public function test_guest_cannot_access_category_endpoints(): void
    {
        $this->getJson('/api/categories')->assertUnauthorized();
    }

    public function test_admin_can_list_categories(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->count(3)->create();

        $this->actingAs($admin)->getJson('/api/categories')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/categories', [
            'name' => ['en' => 'News', 'ar' => 'أخبار'],
            'description' => ['en' => 'Latest news'],
        ])->assertCreated();

        $category = Category::first();
        $this->assertSame('News', $category->getTranslation('name', 'en'));
        $this->assertSame('news', $category->slug);
    }

    public function test_creating_a_category_requires_english_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/categories', [
            'name' => ['ar' => 'أخبار'],
        ])->assertUnprocessable()->assertJsonValidationErrors('name.en');
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['slug' => 'old']);

        $this->actingAs($admin)->putJson("/api/categories/{$category->id}", [
            'name' => ['en' => 'Updated'],
            'slug' => 'updated',
            'is_active' => false,
        ])->assertOk();

        $category->refresh();
        $this->assertSame('updated', $category->slug);
        $this->assertFalse($category->is_active);
    }

    public function test_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/categories/{$category->id}")
            ->assertNoContent();

        $this->assertModelMissing($category);
    }

    public function test_admin_can_fetch_category_options(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => ['en' => 'Alpha'], 'slug' => 'alpha']);

        $this->actingAs($admin)->getJson('/api/categories/options')
            ->assertOk()
            ->assertJsonFragment(['Alpha']);
    }

    public function test_admin_can_fetch_category_stats(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $this->actingAs($admin)->getJson('/api/categories/stats')
            ->assertOk()
            ->assertJson(['total' => 2, 'active' => 1, 'inactive' => 1]);
    }
}
