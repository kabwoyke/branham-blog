<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin User
    |--------------------------------------------------------------------------
    |
    | Credentials for the single admin account created by AdminUserSeeder.
    | They are read from the environment and never stored in code.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Branham Samwel'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
