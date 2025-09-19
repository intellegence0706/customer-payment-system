<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Banking API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the bankcode-jp API used to retrieve
    | bank names and branch names by their codes.
    |
    */

    'bankcode_jp' => [
        'enabled' => env('BANKCODE_JP_ENABLED', true),
        'base_url' => env('BANKCODE_JP_BASE_URL', 'https://apis.bankcode-jp.com/v1'),
        'api_key' => env('BANKCODE_JP_API_KEY'),
        'timeout' => env('BANKCODE_JP_TIMEOUT', 3),
        'endpoints' => [
            'banks' => '/banks/{code}',
            'branches' => '/banks/{bankCode}/branches/{branchCode}'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for bank and branch names to reduce API calls.
    |
    */

    'cache' => [
        'enabled' => env('BANKING_CACHE_ENABLED', true),
        'ttl' => env('BANKING_CACHE_TTL', 604800), // 7 days in seconds
        'prefix' => env('BANKING_CACHE_PREFIX', 'banking'),
    ],
];
