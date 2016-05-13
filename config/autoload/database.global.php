<?php

return [
    'database' => [

        'fetch' => PDO::FETCH_CLASS,

        'default' => 'mysql',

        'connections' => [

            'sqlite' => [
                'driver' => 'sqlite',
                'database' => 'data/expressive.sql',
                'prefix' => '',
            ],

            'mysql' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'port' => '3306',
                'database' => 'expressive',
                'username' => 'expressive',
                'password' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ],

            'pgsql' => [
                'driver' => 'pgsql',
                'host' => 'localhost',
                'port' => '3306',
                'database' => 'expressive',
                'username' => 'expressive',
                'password' => '',
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ],

        ],
    ]
];
