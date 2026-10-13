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

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => config('blog.admin.name'),
            'password' => $password,
            'email_verified_at' => now(),
            'is_admin' => true,
        ])->save();
    }
}
