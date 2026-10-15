<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Demo content for local development and tests. Never run in production.
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ArticleSeeder::class,
        ]);
    }
}
