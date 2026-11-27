<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('siwapas:create-admin {email? : Defaults to ADMIN_EMAIL} {--name= : Defaults to ADMIN_NAME}')]
#[Description('Create the admin account (or promote an existing user to admin) without running any seeder')]
class CreateAdminCommand extends Command
{
    /**
     * Only this account is touched, so it is safe on a database that already holds data. The
     * password comes from ADMIN_PASSWORD or is asked for (hidden); it follows the production
     * password rules.
     */
    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: config('siwapas.admin.email'));
        $name = (string) ($this->option('name') ?: config('siwapas.admin.name') ?: 'Admin');
        $password = (string) (config('siwapas.admin.password') ?: $this->secret('Password admin'));

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', Password::default()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'role' => UserRole::Admin,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info($existed
            ? "Akun {$email} sudah ada; kini menjadi admin dengan password baru."
            : "Akun admin {$email} dibuat. Masuk lewat /admin.");

        return self::SUCCESS;
    }
}
