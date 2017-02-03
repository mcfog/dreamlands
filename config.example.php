<?php

return [
    'db' => [
        'default',
        [
            'dbname' => 'dreamlands',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8',
        ]
    ],
    'env' => 'dev',
    'log' => [
        ['\Monolog\Handler\PHPConsoleHandler'],
    ],
];
