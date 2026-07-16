<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'chroma' => [
        'url' => env('CHROMA_URL', 'http://chromadb:8000'),
    ],

    'rustfs' => [
        'endpoint' => env('RUSTFS_ENDPOINT', 'http://rustfs:9000'),
        'public_endpoint' => env(
            'RUSTFS_PUBLIC_ENDPOINT',
            'http://localhost:9000'
        ),
        'access_key' => env('RUSTFS_ACCESS_KEY'),
        'secret_key' => env('RUSTFS_SECRET_KEY'),
        'region' => env('RUSTFS_REGION', 'us-east-1'),
        'bucket' => env('RUSTFS_BUCKET', 'game-library'),
        'use_path_style_endpoint' => env(
            'RUSTFS_USE_PATH_STYLE_ENDPOINT',
            true
        ),
    ],

];
