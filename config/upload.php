<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for file uploads including
    | maximum file sizes, upload limits, and validation rules.
    |
    */

    'max_file_uploads' => env('MAX_FILE_UPLOADS', 1000),
    'max_input_vars' => env('MAX_INPUT_VARS', 10000),
    'upload_max_filesize' => env('UPLOAD_MAX_FILESIZE', '100M'),
    'post_max_size' => env('POST_MAX_SIZE', '100M'),
    'memory_limit' => env('MEMORY_LIMIT', '512M'),
    'max_execution_time' => env('MAX_EXECUTION_TIME', 300),
    'max_input_time' => env('MAX_INPUT_TIME', 300),

    /*
    |--------------------------------------------------------------------------
    | Image Upload Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to image uploads
    |
    */

    'image' => [
        'max_size' => env('IMAGE_MAX_SIZE', 10240), // 10MB in KB
        'allowed_types' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
        'max_count' => env('IMAGE_MAX_COUNT', 100),
    ],
];
