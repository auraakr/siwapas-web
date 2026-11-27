<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        /*
        | Supabase Storage through its S3-compatible API (Project Settings > Storage > S3 access keys).
        | Media (photos, posters) go to a PUBLIC bucket and are linked directly; payment proofs go to a
        | PRIVATE bucket and only reach admins through the signed /kelola/bukti/{proof} route.
        | Which disk is used is chosen in .env (MEDIA_DISK, PAYMENT_PROOF_DISK), so moving back to the
        | local "public"/"local" disks later needs no code change. No "visibility" is set on purpose:
        | Supabase decides access per bucket and does not use S3 object ACLs.
        */
        'supabase_public' => [
            'driver' => 's3',
            'key' => env('SUPABASE_S3_KEY'),
            'secret' => env('SUPABASE_S3_SECRET'),
            'region' => env('SUPABASE_S3_REGION', 'ap-northeast-1'),
            'endpoint' => env('SUPABASE_S3_ENDPOINT'),
            'bucket' => env('SUPABASE_PUBLIC_BUCKET', 'media'),
            'url' => env('SUPABASE_PUBLIC_URL'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => true,
        ],

        'supabase_private' => [
            'driver' => 's3',
            'key' => env('SUPABASE_S3_KEY'),
            'secret' => env('SUPABASE_S3_SECRET'),
            'region' => env('SUPABASE_S3_REGION', 'ap-northeast-1'),
            'endpoint' => env('SUPABASE_S3_ENDPOINT'),
            'bucket' => env('SUPABASE_PRIVATE_BUCKET', 'payment-proofs'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
