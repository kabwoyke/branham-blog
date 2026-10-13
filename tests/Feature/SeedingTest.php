<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_usable_dataset(): void
    {
        config([
            'blog.admin.email' => 'admin@example.test',
            'blog.admin.password' => 'secret-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(1, User::count());
        $this->assertSame(14, Article::published()->count());
        $this->assertGreaterThanOrEqual(1, Article::featured()->count());
        $this->assertSame('Branham Samwel', Setting::get('site_name'));
        $this->assertSame('branham@narrisinternational.com', Setting::get('contact_email'));
    }

    public function test_seeding_is_idempotent(): void
    {
        config([
            'blog.admin.email' => 'admin@example.test',
            'blog.admin.password' => 'secret-password',
        ]);

        $this->seed(DatabaseSeeder::class);
        Setting::set('tagline', 'Edited in admin');
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(1, User::count());
        $this->assertSame('Edited in admin', Setting::get('tagline'));
    }

    public function test_admin_seeder_refuses_to_run_without_env_credentials(): void
    {
        config(['blog.admin.email' => null, 'blog.admin.password' => null]);

        $this->expectException(RuntimeException::class);

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_password_is_hashed(): void
    {
        config([
            'blog.admin.email' => 'admin@example.test',
            'blog.admin.password' => 'secret-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertNotSame('secret-password', User::first()->password);
    }
}
