<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use toubilib\adapters\controllers\actions\AgendaPraticienAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerIndisponibiliteAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\CreneauxPraticienAction;
use toubilib\adapters\controllers\actions\GetPatientAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\HistoriquePatientAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerIndisponibilitesAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;
use toubilib\adapters\controllers\actions\SupprimerIndisponibiliteAction;
use toubilib\adapters\controllers\middlewares\Cors;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\application\ports\api\PatientServiceInterface;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\RendezVousServiceInterface;

/*
 * API : les recettes pour fabriquer les actions et middlewares Slim (adaptateurs d'entrée).
 */
return [
    // ── middlewares ──
    Cors::class => function (ContainerInterface $c) {
        return new Cors(new ResponseFactory());
    },

    // ── praticiens ──
    ListerPraticiensAction::class => function (ContainerInterface $c) {
        return new ListerPraticiensAction($c->get(PraticienServiceInterface::class));
    },
    GetPraticienAction::class => function (ContainerInterface $c) {
        return new GetPraticienAction($c->get(PraticienServiceInterface::class));
    },
    AgendaPraticienAction::class => function (ContainerInterface $c) {
        return new AgendaPraticienAction($c->get(RendezVousServiceInterface::class));
    },
    CreneauxPraticienAction::class => function (ContainerInterface $c) {
        return new CreneauxPraticienAction($c->get(RendezVousServiceInterface::class));
    },

    // ── indisponibilités ──
    ListerIndisponibilitesAction::class => function (ContainerInterface $c) {
        return new ListerIndisponibilitesAction($c->get(IndisponibiliteServiceInterface::class));
    },
    CreerIndisponibiliteAction::class => function (ContainerInterface $c) {
        return new CreerIndisponibiliteAction($c->get(IndisponibiliteServiceInterface::class));
    },
    SupprimerIndisponibiliteAction::class => function (ContainerInterface $c) {
        return new SupprimerIndisponibiliteAction($c->get(IndisponibiliteServiceInterface::class));
    },

    // ── patients ──
    GetPatientAction::class => function (ContainerInterface $c) {
        return new GetPatientAction($c->get(PatientServiceInterface::class));
    },
    HistoriquePatientAction::class => function (ContainerInterface $c) {
        return new HistoriquePatientAction($c->get(PatientServiceInterface::class));
    },

    // ── rendez-vous ──
    GetRendezVousAction::class => function (ContainerInterface $c) {
        return new GetRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
    CreerRendezVousAction::class => function (ContainerInterface $c) {
        return new CreerRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
    AnnulerRendezVousAction::class => function (ContainerInterface $c) {
        return new AnnulerRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
    HonorerRendezVousAction::class => function (ContainerInterface $c) {
        return new HonorerRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
    IgnorerRendezVousAction::class => function (ContainerInterface $c) {
        return new IgnorerRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
];
