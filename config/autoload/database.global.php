<?php

use App\Database;
use function App\Foundation\env;

return [
    'dependencies' => [
        'invokables' => [
            'App\Database\MigrationCreator'
        ],

        'factories' => [
            Illuminate\Database\ConnectionResolverInterface::class => Database\ConnectionResolverFactory::class,
            Illuminate\Database\ConnectionInterface::class => Database\ConnectionResolverFactory::class,
            'Illuminate\Database\Migrations\MigrationRepositoryInterface' => 'App\Database\DatabaseMigrationRepositoryFactory',
            'App\Database\Migrator' => 'App\Database\MigratorFactory',
            'App\Database\Console\Commands\InstallCommand' => 'App\Database\Console\ConsoleFactory',
            'App\Database\Console\Commands\MakeMigration' => 'App\Database\Console\ConsoleFactory',
            'App\Database\Console\Commands\Migrate' => 'App\Database\Console\ConsoleFactory',
        ]
    ],

    'database' => [

        'fetch' => PDO::FETCH_CLASS,

        'default' => 'sqlite',

        'migrations' => 'migrations',

        'connections' => [

            'sqlite' => [
                'driver' => 'sqlite',
                'database' => realpath('database/expressive.db'),
                'prefix' => '',
            ],

            'mysql' => [
                'driver' => 'mysql',
                'host'   => env('DB_HOST', 'localhost'),
                'port'   => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ],

            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ],

        ],
    ]
];
