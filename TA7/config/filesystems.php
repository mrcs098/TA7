<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => ['driver' => 'local', 'root' => storage_path('app/private'), 'throw' => false],
        'public' => [
            'driver' => 'local',
            'root' => env('PUBLIC_STORAGE_PATH', storage_path('app/public')),
            'url' => rtrim(env('APP_URL', 'http://localhost:8000'), '/').'/storage',
            'visibility' => 'public',
            'throw' => true,
        ],
    ],
    'links' => [public_path('storage') => env('PUBLIC_STORAGE_PATH', storage_path('app/public'))],
];
