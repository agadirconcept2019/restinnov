<?php

return [
    'supported' => ['en', 'fr', 'es'],
    'default' => env('APP_LOCALE', 'en'),
    'fallback' => env('APP_FALLBACK_LOCALE', 'en'),
];
