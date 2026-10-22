<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default imgproxy instance
    |--------------------------------------------------------------------------
    |
    | The name of the imgproxy instance used when no instance is specified.
    | It must reference one of the instances defined below.
    |
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | imgproxy instances
    |--------------------------------------------------------------------------
    |
    | Each instance points at one imgproxy server. The "default" instance
    | reads its connection details from the environment, so the package
    | works after setting IMGPROXY_URL, IMGPROXY_KEY, and IMGPROXY_SALT.
    |
    | Supported options:
    |
    |   - url:             Base URL of the imgproxy server, without trailing slash.
    |   - key:             Hex-encoded HMAC key used for signing, or null to
    |                      generate unsigned URLs.
    |   - salt:            Hex-encoded salt used for signing.
    |   - signature_size:  Number of signature bytes to keep (matches the
    |                      server's IMGPROXY_SIGNATURE_SIZE), or null for full.
    |   - encoding:        Source encoding: "base64" (URL-safe, no padding)
    |                      or "plain" (percent-encoded source).
    |
    */

    'instances' => [
        'default' => [
            'url' => env('IMGPROXY_URL'),
            'key' => env('IMGPROXY_KEY'),
            'salt' => env('IMGPROXY_SALT'),
            'signature_size' => null,
            'encoding' => 'base64',
        ],

        // Same server reached from inside the Docker network, used to fetch processed uploads
        'internal' => [
            'url' => env('IMGPROXY_INTERNAL_URL', env('IMGPROXY_URL')),
            'key' => env('IMGPROXY_KEY'),
            'salt' => env('IMGPROXY_SALT'),
            'signature_size' => null,
            'encoding' => 'base64',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | Named sets of processing options, reusable across instances. Each
    | preset maps an option name to its value and composes onto the URL
    | builder before per-URL overrides.
    |
    */

    'presets' => [

        // Applied to uploaded images before they are stored (see config/uploads.php)
        'optimize' => [
            'resize' => 'fit',
            'width' => 2560,
            'height' => 2560,
            'autoRotate' => true,
            'stripMetadata' => true,
            'quality' => 90,
            'format' => 'webp',
        ],

        'avatar' => [
            'resize' => 'fill',
            'width' => 300,
            'height' => 300,
            'format' => 'webp',
        ],

    ],

];
