<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(rand(5, 9)), '.');

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentences(2, true),
            'body' => implode("\n\n", fake()->paragraphs(rand(4, 8))),
            'type' => fake()->randomElement(ArticleType::cases()),
            'featured_image' => null,
            'featured_image_alt' => null,
            'is_featured' => false,
            'status' => ArticleStatus::Published,
            'published_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ArticleStatus::Draft, 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state([
            'status' => ArticleStatus::Scheduled,
            'published_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    public function ofType(ArticleType $type): static
    {
        return $this->state(['type' => $type]);
    }
}
