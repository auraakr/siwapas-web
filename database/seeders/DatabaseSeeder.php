<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = config('siwapas.admin.email');
        $password = config('siwapas.admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL and ADMIN_PASSWORD are not set; skipping admin user.');

            return;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => config('siwapas.admin.name'),
            'password' => $password,
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ])->save();
    }
}
