<?php
return [
    'phwoolcon/auth' => [
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
