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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Japanese Bank and Branch API Services
    |--------------------------------------------------------------------------
    |
    | Configuration for Japanese financial institution APIs
    |
    */

    'japanese_bank_api' => [
        'url' => env('JAPANESE_BANK_API_URL', 'https://api.jba.or.jp/banks'),
        'timeout' => env('JAPANESE_BANK_API_TIMEOUT', 10),
        'api_key' => env('JAPANESE_BANK_API_KEY'),
    ],

    'japanese_branch_api' => [
        'url' => env('JAPANESE_BRANCH_API_URL', 'https://api.jba.or.jp/branches'),
        'timeout' => env('JAPANESE_BRANCH_API_TIMEOUT', 10),
        'api_key' => env('JAPANESE_BRANCH_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Bank and Branch API Services (for backward compatibility)
    |--------------------------------------------------------------------------
    */

    'bank_api' => [
        'url' => env('BANK_API_URL', 'https://api.example.com/banks'),
        'timeout' => env('BANK_API_TIMEOUT', 10),
        'api_key' => env('BANK_API_KEY'),
    ],

    'branch_api' => [
        'url' => env('BRANCH_API_URL', 'https://api.example.com/branches'),
        'timeout' => env('BRANCH_API_TIMEOUT', 10),
        'api_key' => env('BRANCH_API_KEY'),
    ],

];
