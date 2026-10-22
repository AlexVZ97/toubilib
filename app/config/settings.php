<?php
declare(strict_types=1);

return [
    'displayErrorDetails' => true,

    'db.config' => [
        'driver'   => $_ENV['lib.driver'],
        'host'     => $_ENV['lib.host'],
        'database' => $_ENV['lib.database'],
        'username' => $_ENV['lib.username'],
        'password' => $_ENV['lib.password'],
    ],
];
