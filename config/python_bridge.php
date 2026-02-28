<?php

return [

    'enabled' => env('PYTHON_BRIDGE_ENABLED', false),

    'endpoint' => env('PYTHON_BRIDGE_ENDPOINT', 'http://localhost:8001/api/process'),

    'timeout' => (int) env('PYTHON_BRIDGE_TIMEOUT', 30),

    'max_retries' => (int) env('PYTHON_BRIDGE_MAX_RETRIES', 3),

    'backoff' => (int) env('PYTHON_BRIDGE_BACKOFF', 60),

    'environment' => env('PYTHON_BRIDGE_ENV', 'dev'),

];
