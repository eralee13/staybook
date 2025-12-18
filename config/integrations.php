<?php

return [
    'exely' => [
        'base_url' => env('EXELY_BASE_URL', 'https://api.exely.com'),
        'api_key'  => env('EXELY_API_KEY'),
        'timeout'  => env('EXELY_TIMEOUT', 10),
        'cache_ttl' => [
            'hotels'       => 60, // мин
            'availability' => 5,  // мин
            'rates'        => 5,
            'reservations' => 1,
        ],
    ],

    // тут же потом добавишь tourmind/emerging/hotelstar
];