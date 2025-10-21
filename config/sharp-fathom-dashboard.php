<?php

// config for Code16/SharpFathomDashboard
return [
    /*
     * Credentials to Fathom API
     */
    'fathom_site_id' => env('FATHOM_SITE_ID'),
    'fathom_api_key' => env('FATHOM_API_KEY'),
    'fathom_api_url' => env('FATHOM_API_URL', 'https://api.usefathom.com/v1/'),

    /*
     * Cache configuration
     */
    'cache' => env('FATHOM_CACHE_ENABLED', true),
    // cache ttl in minutes
    'cache_ttl' => env('FATHOM_CACHE_TTL', 60),

    /*
     * Miscs configuration
     */
    'fathom_access_url' => env('FATHOM_ACCESS_URL', null)
];
