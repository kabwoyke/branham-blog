<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'Society and Behaviour' => 'How people act, relate and change in everyday life.',
        'Business and Strategy' => 'Markets, organisations and the decisions that shape them.',
        'Generations and Lifestyles' => 'How age, values and ways of living are shifting.',
        'Institutions and Trust' => 'The structures societies rely on, and whether people still believe in them.',
        'Notes and Observations' => 'Short reflections on what is noticed along the way.',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $name => $description) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
