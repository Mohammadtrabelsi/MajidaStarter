<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CategoryService::class);
    }

    public function test_create_derives_slug_from_name_when_none_given(): void
    {
        $category = $this->service->create(['name' => ['en' => 'Breaking News']]);

        $this->assertSame('breaking-news', $category->slug);
    }

    public function test_create_prefers_an_explicit_slug(): void
    {
        $category = $this->service->create([
            'name' => ['en' => 'Breaking News'],
            'slug' => 'custom-slug',
        ]);

        $this->assertSame('custom-slug', $category->slug);
    }

    public function test_slug_falls_back_to_default_when_name_is_not_sluggable(): void
    {
        $category = $this->service->create(['name' => ['en' => '!!!']]);

        $this->assertSame('category', $category->slug);
    }

    public function test_slug_uses_first_translation_when_english_is_absent(): void
    {
        // With no 'en' key the null-coalesce falls through to the first
        // available translation as the slug source.
        $category = $this->service->create(['name' => ['ar' => 'World']]);

        $this->assertSame('world', $category->slug);
    }

    public function test_slug_is_made_unique_with_incrementing_suffix(): void
    {
        $this->service->create(['name' => ['en' => 'Sports']]);
        $second = $this->service->create(['name' => ['en' => 'Sports']]);
        $third = $this->service->create(['name' => ['en' => 'Sports']]);

        $this->assertSame('sports', Category::orderBy('id')->first()->slug);
        $this->assertSame('sports-2', $second->slug);
        $this->assertSame('sports-3', $third->slug);
    }

    public function test_update_can_keep_the_same_slug_without_appending_a_suffix(): void
    {
        $category = $this->service->create(['name' => ['en' => 'Tech'], 'slug' => 'tech']);

        $updated = $this->service->update($category, ['name' => ['en' => 'Tech News'], 'slug' => 'tech']);

        $this->assertSame('tech', $updated->slug);
    }

    public function test_update_bumps_slug_when_it_collides_with_another_category(): void
    {
        $this->service->create(['name' => ['en' => 'First'], 'slug' => 'first']);
        $second = $this->service->create(['name' => ['en' => 'Second'], 'slug' => 'second']);

        $updated = $this->service->update($second, ['name' => ['en' => 'Second'], 'slug' => 'first']);

        $this->assertSame('first-2', $updated->slug);
    }

    public function test_delete_removes_the_category(): void
    {
        $category = Category::factory()->create();

        $this->service->delete($category);

        $this->assertModelMissing($category);
    }

    public function test_options_returns_id_keyed_names_ordered_by_slug(): void
    {
        $b = Category::factory()->create(['name' => ['en' => 'Bravo'], 'slug' => 'bravo']);
        $a = Category::factory()->create(['name' => ['en' => 'Alpha'], 'slug' => 'alpha']);

        $options = $this->service->options();

        $this->assertSame([$a->id => 'Alpha', $b->id => 'Bravo'], $options);
    }

    public function test_search_paginated_returns_everything_for_null_search(): void
    {
        Category::factory()->count(4)->create();

        $this->assertSame(4, $this->service->searchPaginated(null)->total());
    }

    public function test_search_paginated_matches_slug(): void
    {
        Category::factory()->create(['slug' => 'unique-target-slug']);
        Category::factory()->create(['slug' => 'other-slug']);

        $this->assertSame(1, $this->service->searchPaginated('unique-target')->total());
    }

    public function test_stats_returns_zeroes_when_empty(): void
    {
        $this->assertSame(
            ['total' => 0, 'active' => 0, 'inactive' => 0],
            $this->service->stats()
        );
    }

    public function test_stats_counts_active_and_inactive(): void
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->inactive()->create();

        $stats = $this->service->stats();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(2, $stats['active']);
        $this->assertSame(1, $stats['inactive']);
    }
}
