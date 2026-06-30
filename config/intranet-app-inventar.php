<?php

use App\Models\User;

return [
    'user_model' => env('INVENTAR_USER_MODEL', User::class),
    'roles' => [
        'admin' => [
            'name' => 'App-Inventar-Admin',
            'permissions' => [
                'see-app-inventar',
                'manage-app-inventar',
            ],
        ],
        'user' => [
            'name' => 'App-Inventar-Benutzer',
            'permissions' => [
                'see-app-inventar',
            ],
        ],
    ],
];
