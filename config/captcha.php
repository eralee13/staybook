<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'version' => 'v3',
    'score'   => 0.5,
    'options' => [
        'timeout' => 30,
    ],
];
