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
        'hours' => env('SIWAPAS_CONTACT_HOURS', '09.00–23.00 WIB'),
        'maps_url' => env('SIWAPAS_MAPS_URL'),
        'instagram' => env('SIWAPAS_INSTAGRAM_URL'),
        'facebook' => env('SIWAPAS_FACEBOOK_URL'),
        'youtube' => env('SIWAPAS_YOUTUBE_URL'),
    ],

    'landing' => [
        'video_url' => env('SIWAPAS_HERO_VIDEO_URL'),
    ],

    /*
    | Visit information shown on the landing page. The managers can change the contact details,
    | opening hours and admission text under Pengaturan Situs; these are the fallbacks.
    */
    'visit' => [
        'admission' => [
            'id' => 'Gratis untuk kunjungan reguler.',
            'en' => 'Free for regular visits.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Defaults
    |--------------------------------------------------------------------------
    */

    'display_timezone' => 'Asia/Jakarta',

    /*
    | Sample ("Contoh") data is only seeded with APP_ENV=local on a non-Supabase database. Set
    | SEED_SAMPLE_DATA=true to seed it anywhere on purpose (for example a demo database).
    */
    'seed_sample_data' => (bool) env('SEED_SAMPLE_DATA', false),

    /*
    | Languages visitors can switch between on the public site. Content columns hold one
    | translation per language; the default language is shown until a visitor chooses another.
    */
    'locales' => ['id', 'en'],

    'default_locale' => 'id',

    'rental_unit' => 'day',

    'payment' => [
        'hold_hours' => 24,

        /*
        | Reservations are paid with a down payment of this share of the full price; the managers can
        | change it in the admin panel (stored in site_settings). Event tickets are always paid in full.
        */
        'dp_percentage' => 50,

        /*
        | The down payment is due at the start of the check-in day at the latest. Bookings that would
        | leave less time than this to pay are refused online.
        */
        'min_payment_window_minutes' => 120,

        'unique_code_min' => 1,
        'unique_code_max' => 999,
        'verification_target_hours' => 24,
        'proof_disk' => env('PAYMENT_PROOF_DISK', 'local'),
        'proof_max_kb' => 5120,
        'proof_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

];
