<?php

use function App\Foundation\env;

return [
    'dependencies' => [
        'factories' => [
            'Illuminate\Contracts\Queue\Factory' => 'App\Queue\QueueServiceFactory',
            'Illuminate\Contracts\Queue\Queue' => 'App\Queue\QueueServiceFactory',
            'App\Queue\Failed\FailedJobProviderInterface' => 'App\Queue\QueueServiceFactory',
            'App\Queue\Processor\Listener' => 'App\Queue\QueueServiceFactory'
        ]
    ],
    'queue' => [
        'default' => env('QUEUE_DRIVER', 'database'),
        'connections' => [

            'sync' => [
                'driver' => 'sync',
            ],

            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'expire' => 60,
            ],

            'beanstalkd' => [
                'driver' => 'beanstalkd',
                'host' => 'localhost',
                'queue' => 'default',
                'ttr' => 60,
            ],
        ],

        'failed' => [
            'database' => env('DB_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ],
    ],
];
