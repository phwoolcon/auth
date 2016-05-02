<?php

return [
    'adapter' => 'Generic',
    'options' => [
        'user_model' => 'Phwoolcon\User',
        'login_form' => '',
        'register_form' => '',
    ],
    'routes' => [
        'GET' => [
            'account/login' => function () {},
            'account/logout' => function () {},
        ],
        'POST' => [
            'account/register' => function () {},
            'account/login' => function () {},
        ],
    ],
];
