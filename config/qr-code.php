<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default QR Code Generator
    |--------------------------------------------------------------------------
    |
    | This option controls the default QR Code generator used.
    |
    */
    'default' => env('QRCODE_DEFAULT', 'imagick'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Generators
    |--------------------------------------------------------------------------
    |
    | Here you may configure the drivers and their settings for generating
    | QR Codes.
    |
    */
    'generators' => [
        'imagick' => [
            'driver' => 'imagick',
        ],
        'gd' => [
            'driver' => 'gd',
        ],
        'svg' => [
            'driver' => 'svg',
        ],
    ],
];