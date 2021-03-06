<?php

use function App\Foundation\env;

return [
    'dependencies' => [
        'factories' => [
            'Illuminate\Contracts\Mail\Mailer' =>
                'App\Foundation\Mail\MailerFactory'
        ]
    ],

    'mail' => [

        'driver' => env('MAIL_DRIVER', 'smtp'),

        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),

        'port' => env('MAIL_PORT', 587),

        'from' => ['address' => null, 'name' => null],

        'encryption' => env('MAIL_ENCRYPTION', 'tls'),

        'username' => env('MAIL_USERNAME'),

        'password' => env('MAIL_PASSWORD'),

        'sendmail' => '/usr/sbin/sendmail -bs',

    ]

];
