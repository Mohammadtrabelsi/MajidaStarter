<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_categories_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/categories')->assertOk();
    }

    public function test_non_admin_cannot_view_categories_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.categories.create')
            ->set('name.en', 'News')
            ->set('name.ar', 'أخبار')
            ->set('description.en', 'Latest news')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::first();

        $this->assertNotNull($category);
        $this->assertSame('News', $category->getTranslation('name', 'en'));
        $this->assertSame('أخبار', $category->getTranslation('name', 'ar'));
        $this->assertSame('news', $category->slug);
    }

    public function test_category_creation_requires_english_name(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.categories.create')
            ->set('name.en', '')
            ->call('save')
            ->assertHasErrors(['name.en' => 'required']);
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.categories.edit', ['category' => $category])
            ->set('name.en', 'Updated')
            ->set('slug', 'updated-slug')
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertSame('Updated', $category->getTranslation('name', 'en'));
        $this->assertSame('updated-slug', $category->slug);
        $this->assertFalse($category->is_active);
    }

    public function test_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.categories.index')
            ->call('deleteCategory', $category->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($category);
    }

    public function test_slug_is_generated_uniquely(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['slug' => 'news']);

        Livewire::actingAs($admin)
            ->test('pages::admin.categories.create')
            ->set('name.en', 'News')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'news-2']);
    }
}
