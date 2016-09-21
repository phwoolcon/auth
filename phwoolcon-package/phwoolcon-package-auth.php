<?php
return [
    'phwoolcon/auth' => [
        'config' => 'auth.php',
        'assets' => 'phwoolcon-auth',
        'di' => [
            10 => 'di.php',
        ],
        'class_aliases' => [
            10 => [
                'Auth' => 'Phwoolcon\Auth\Auth',
            ],
        ],
    ],
];
