<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeded Admin
    |--------------------------------------------------------------------------
    |
    | Credentials used by DatabaseSeeder to create the first admin user.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Defaults
    |--------------------------------------------------------------------------
    */

    'rental_unit' => 'day',

    'payment' => [
        'hold_hours' => 24,
        'unique_code_min' => 1,
        'unique_code_max' => 999,
        'verification_target_hours' => 24,
        'proof_disk' => env('PAYMENT_PROOF_DISK', 'local'),
        'proof_max_kb' => 5120,
        'proof_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

];
