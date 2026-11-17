<?php

namespace Tests\Feature;

use App\Console\Commands\PackageForHostinger;
use Tests\TestCase;

class PackageForHostingerTest extends TestCase
{
    public function test_package_ships_the_app_but_not_secrets_or_development_files(): void
    {
        $files = (new PackageForHostinger)->deployableFiles();

        foreach (['artisan', 'composer.lock', 'app/Models/Article.php', 'public/index.php', 'public/.htaccess', 'routes/web.php', 'storage/framework/views/.gitignore', '.env.hostinger.example'] as $expected) {
            $this->assertContains($expected, $files);
        }

        foreach ($files as $file) {
            $this->assertNotSame('.env', $file);
            $this->assertNotSame('public/hot', $file);
            $this->assertDoesNotMatchRegularExpression('#^(tests|node_modules|vendor|\.git|\.claude|resources/js)/|\.sqlite$|^CLAUDE\.md$#', $file);
        }
    }
}
