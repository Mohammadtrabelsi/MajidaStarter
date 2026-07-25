<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Services\CategoryService;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_can_be_searched_by_translated_title(): void
    {
        Post::factory()->create([
            'title' => ['en' => 'Zebra Chronicles', 'ar' => 'قصص'],
            'slug' => 'an-unrelated-slug',
        ]);
        Post::factory()->create([
            'title' => ['en' => 'Something Else', 'ar' => 'شيء'],
            'slug' => 'another-unrelated-slug',
        ]);

        $results = app(PostService::class)->searchPaginated('Zebra');

        $this->assertSame(1, $results->total());
        $this->assertSame('Zebra Chronicles', $results->first()->getTranslation('title', 'en'));
    }

    public function test_categories_can_be_searched_by_translated_name(): void
    {
        Category::factory()->create([
            'name' => ['en' => 'Astronomy', 'ar' => 'علم الفلك'],
            'slug' => 'unrelated-cat-slug',
        ]);
        Category::factory()->create([
            'name' => ['en' => 'Cooking', 'ar' => 'طبخ'],
            'slug' => 'another-cat-slug',
        ]);

        $results = app(CategoryService::class)->searchPaginated('Astronomy');

        $this->assertSame(1, $results->total());
        $this->assertSame('Astronomy', $results->first()->getTranslation('name', 'en'));
    }
}
