<?php

return array_merge_recursive([
    'db' => [
        'default',
        [
            'dbname' => 'dreamlands',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ]
    ],
    'container' => [
        'Stash\Driver\FileSystem:options' => [
            'path' => __DIR__ . '/data/cache',
        ],
    ],
    'env' => 'dev',
    'log' => [
        [
            '\Monolog\Handler\RotatingFileHandler',
            [
                __DIR__ . '/data/log/log'
            ]
        ],
        ['\Monolog\Handler\PHPConsoleHandler'],
    ],
//    'env' => 'prod',
//    'log' => [
//        [
//            '\Monolog\Handler\RotatingFileHandler',
//            [
//                __DIR__ . '/data/log/log'
//            ]
//        ],
//    ],
], json_decode($_SERVER['DCONFIG'], true));
