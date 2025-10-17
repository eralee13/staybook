<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Filters
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party amenities such
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

    'exely' => [
        'key'  => env('EXELY_API_KEY'),
        'base' => rtrim(env('EXELY_BASE_URL', env('EXELY_BASE', 'https://connect.hopenapi.com/api')), '/'),
        'checkin_time'  => env('EXELY_CHECKIN_TIME',  '14:00'),
        'checkout_time' => env('EXELY_CHECKOUT_TIME', '12:00'),
    ],

    'tourmind' => [
        'api_url'   => env('TOURMIND_API_URL'),
        'agent'     => env('TM_AGENT_CODE'),
        'username'  => env('TM_USER_NAME'),
        'password'  => env('TM_PASSWORD'),
    ],

    'main' => [
        'coef' => env('MAIN_COEF'),
    ],

    'fxkg' => [
        'url'   => env('FXKG_API_URL', 'https://data.fx.kg/api/v1'),
        'token' => env('FXKG_API_TOKEN'),
    ],

    'hotelstar' => [
        'url' => env('HOTELSTAR_API_URL'),
        'token' => env('HOTELSTAR_API_TOKEN'),
    ],
];