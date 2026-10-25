<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'body' => fake()->paragraph(),
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['approved_at' => null]);
    }
}
