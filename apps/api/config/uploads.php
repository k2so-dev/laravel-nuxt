<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Uploads
    |--------------------------------------------------------------------------
    |
    | When "optimize" is enabled, uploaded images are processed by imgproxy
    | with the "optimize" preset (config/laravel-imgproxy.php) and only the
    | result is stored. Otherwise the original file is stored as is.
    |
    | max_size is in kilobytes. Keep octane.swoole.options.package_max_length
    | above it.
    |
    */

    'disk' => 'public',

    'optimize' => (bool) env('UPLOAD_OPTIMIZE', true),

    'max_size' => 20480,

];
