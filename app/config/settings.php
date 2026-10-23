<?php
declare(strict_types=1);

return [
    'displayErrorDetails' => true,

    'logs.file'  => __DIR__ . '/../logs/toubilib.log',
    'logs.level' => \Monolog\Level::Info,

    'db.config' => [
        'driver'   => $_ENV['lib.driver'],
        'host'     => $_ENV['lib.host'],
        'database' => $_ENV['lib.database'],
        'username' => $_ENV['lib.username'],
        'password' => $_ENV['lib.password'],
    ],
];
