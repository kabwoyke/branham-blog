<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('blog.admin.email');
        $password = config('blog.admin.password');

        if (! $email || ! $password) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding the admin user.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('blog.admin.name'),
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }
}
