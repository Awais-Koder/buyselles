<?php

return [
    'exchange_rate_api_url' => env('EXCHANGE_RATE_API_URL', 'https://open.er-api.com/v6/latest/USD'),
    'exchange_rate_api_timeout' => (int) env('EXCHANGE_RATE_API_TIMEOUT', 30),
    'sync_currencies' => explode(',', env('SYNC_CURRENCIES', 'JOD,SAR,AED')),
];
