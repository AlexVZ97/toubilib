<?php
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use toubilib\adapters\persistence\PgIndisponibiliteRepository;
use toubilib\adapters\persistence\PgPatientRepository;
use toubilib\adapters\persistence\PgPraticienRepository;
use toubilib\adapters\persistence\PgRendezVousRepository;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\application\ports\api\PatientServiceInterface;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\IndisponibiliteRepositoryInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\IndisponibiliteService;
use toubilib\application\usecases\PatientService;
use toubilib\application\usecases\PraticienService;
use toubilib\application\usecases\RendezVousService;
use toubilib\application\validators\CreateRdvValidator;

/*
 * SERVICES : les "recettes" pour fabriquer la connexion PDO, les repositories
 * et les services métier.
 * Clé = ce qu'on demande (souvent une INTERFACE)
 * Valeur = fonction qui fabrique l'objet CONCRET.
 */
return [

    // ── logger : interface PSR-3 → implantation Monolog ──
    LoggerInterface::class => function (ContainerInterface $c) {
        $logger = new Logger('toubilib');
        $logger->pushHandler(new StreamHandler($c->get('logs.file'), $c->get('logs.level')));
        return $logger;
    },

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
    PatientRepositoryInterface::class => function (ContainerInterface $c) {
        return new PgPatientRepository($c->get('toubilib.pdo'));
    },
    IndisponibiliteRepositoryInterface::class => function (ContainerInterface $c) {
        return new PgIndisponibiliteRepository($c->get('toubilib.pdo'));
    },

    // ── validateurs ──
    CreateRdvValidator::class => function (ContainerInterface $c) {
        return new CreateRdvValidator(
            $c->get(PraticienRepositoryInterface::class),
            $c->get(PatientRepositoryInterface::class),
        );
    },

    // ── services métier : port API → classe du usecase ──
    RendezVousServiceInterface::class => function (ContainerInterface $c) {
        return new RendezVousService(
            $c->get(RendezVousRepositoryInterface::class),
            $c->get(PraticienRepositoryInterface::class),
            $c->get(IndisponibiliteRepositoryInterface::class),
            $c->get(CreateRdvValidator::class),
            $c->get(LoggerInterface::class),
        );
    },
    PraticienServiceInterface::class => function (ContainerInterface $c) {
        return new PraticienService($c->get(PraticienRepositoryInterface::class));
    },
    PatientServiceInterface::class => function (ContainerInterface $c) {
        return new PatientService(
            $c->get(PatientRepositoryInterface::class),
            $c->get(RendezVousRepositoryInterface::class),
        );
    },
    IndisponibiliteServiceInterface::class => function (ContainerInterface $c) {
        return new IndisponibiliteService(
            $c->get(PraticienRepositoryInterface::class),
            $c->get(IndisponibiliteRepositoryInterface::class),
            $c->get(RendezVousRepositoryInterface::class),
            $c->get(LoggerInterface::class),
        );
    },
];
