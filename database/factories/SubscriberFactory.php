<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'confirmed_at' => now(),
            'unsubscribe_token' => Str::random(64),
        ];
    }

    public function unconfirmed(): static
    {
        return $this->state(['confirmed_at' => null]);
    }
}
