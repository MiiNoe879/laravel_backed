<?php

return [

    'sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'validation_rules' => [
            'phonenumber' => 'required',
        ]
    ],

    'login' => [
        'validation_rules' => [
            'pincode' => 'required'
        ]
    ],

    'updateuser' => [
        'validation_rules' => [
            
        ]
    ],

    'forgot_password' => [
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    'reset_password' => [
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]
    ],

    'image' => [
        'validation_rules' => [
            'description' => 'required',
            'location' => 'required'
        ]
    ],

    'report' => [
        'validation_rules' => [
            'reason' => 'required',
            'image_id' => 'required'
        ]
    ],
];
