<?php

return [
    'provider' => 'vinbox',
    'vinbox' => [
        'url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
        'template_id' => env('SMS_TEMPLATE_ID'),
        'entity_id' => env('SMS_ENTITY_ID'),
        'timeout' => env('SMS_TIMEOUT', 30),
    ],  
];