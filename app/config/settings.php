<?php
declare(strict_types=1);

/*
 * SETTINGS : les variables de configuration.
 * Les valeurs viennent du fichier config/.env (chargé par bootstrap.php).
 */
return [
    'displayErrorDetails' => true,   // mettre false en production

    // ── logs (Monolog) ──
    'logs.file'  => __DIR__ . '/../logs/toubilib.log',
    'logs.level' => \Monolog\Level::Info,   // Debug < Info < Notice < Warning < Error < …

    'db.config' => [
        'driver'   => $_ENV['lib.driver'],
        'host'     => $_ENV['lib.host'],
        'database' => $_ENV['lib.database'],
        'username' => $_ENV['lib.username'],
        'password' => $_ENV['lib.password'],
    ],
];
