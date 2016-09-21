<?php
return [
    'phwoolcon/auth' => [
        'config' => 'auth.php',
        'assets' => 'phwoolcon-auth',
        'di' => [
            10 => 'di.php',
        ],
        'class_aliases' => [
            'Auth' => 'Phwoolcon\Auth\Auth',
        ],
    ],
];
