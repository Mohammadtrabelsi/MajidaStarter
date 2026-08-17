<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => ['en' => $title, 'ar' => $title],
            'excerpt' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'body' => ['en' => fake()->paragraphs(3, true), 'ar' => fake()->paragraphs(3, true)],
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'status' => Post::STATUS_DRAFT,
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
