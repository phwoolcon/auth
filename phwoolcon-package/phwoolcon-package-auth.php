<?php
return [
    'phwoolcon/auth' => [
        'config' => 'auth.php',
        'assets' => [
            'phwoolcon-auth/sso.js',
            'phwoolcon-auth/sso.min.js',
        ],
        'di' => [
            10 => 'di.php',
        ],
    ],
];
