<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\persistence\PgPraticienRepository;
use toubilib\adapters\persistence\PgRendezVousRepository;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\PraticienService;
use toubilib\application\usecases\RendezVousService;

/*
 * SERVICES : les "recettes" pour fabriquer la connexion PDO, les repositories
 * et les services métier.
 * Clé = ce qu'on demande (souvent une INTERFACE)
 * Valeur = fonction qui fabrique l'objet CONCRET.
 */
return [

    // ── connexion à la base ──
    'toubilib.pdo' => function (ContainerInterface $c): PDO {
        $db = $c->get('db.config');
        $dsn = "{$db['driver']}:host={$db['host']};dbname={$db['database']}";
        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    },

    // ── repositories (adaptateurs de sortie) : port SPI → classe Pg ──
    RendezVousRepositoryInterface::class => function (ContainerInterface $c) {
        return new PgRendezVousRepository($c->get('toubilib.pdo'));
    },
    PraticienRepositoryInterface::class => function (ContainerInterface $c) {
        return new PgPraticienRepository($c->get('toubilib.pdo'));
    },

    // ── services métier : port API → classe du usecase ──
    RendezVousServiceInterface::class => function (ContainerInterface $c) {
        return new RendezVousService($c->get(RendezVousRepositoryInterface::class));
    },
    PraticienServiceInterface::class => function (ContainerInterface $c) {
        return new PraticienService($c->get(PraticienRepositoryInterface::class));
    },
];
