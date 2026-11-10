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
    | Public Contact Details
    |--------------------------------------------------------------------------
    |
    | Shown in the site header, footer and landing page. Empty values are hidden.
    | WhatsApp numbers use the local format, e.g. 081234567890.
    |
    */

    'contact' => [
        'phone' => env('SIWAPAS_CONTACT_PHONE'),
        'whatsapp' => env('SIWAPAS_CONTACT_WHATSAPP'),
        'email' => env('SIWAPAS_CONTACT_EMAIL'),
        'address' => env('SIWAPAS_CONTACT_ADDRESS'),
        'hours' => env('SIWAPAS_CONTACT_HOURS'),
        'maps_url' => env('SIWAPAS_MAPS_URL'),
        'instagram' => env('SIWAPAS_INSTAGRAM_URL'),
        'facebook' => env('SIWAPAS_FACEBOOK_URL'),
        'youtube' => env('SIWAPAS_YOUTUBE_URL'),
    ],

    'landing' => [
        'video_url' => env('SIWAPAS_HERO_VIDEO_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Defaults
    |--------------------------------------------------------------------------
    */

    'display_timezone' => 'Asia/Jakarta',

    /*
    | Languages visitors can switch between on the public site. Content columns hold one
    | translation per language; the default language is shown until a visitor chooses another.
    */
    'locales' => ['id', 'en'],

    'default_locale' => 'id',

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
