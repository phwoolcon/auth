<?php
return [
    'phwoolcon/auth' => [
        'di' => [
            10 => 'di.php',
        ],
        'class_aliases' => [
            10 => [
                'Auth' => 'Phwoolcon\Auth\Auth',
            ],
        ],
        'assets' => [
            'sso-js'      => [
                'phwoolcon-auth/sso.min.js',
            ],
        ],
    ],
];
