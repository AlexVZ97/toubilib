<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\RendezVousServiceInterface;

/*
 * API : les recettes pour fabriquer les actions Slim (adaptateurs d'entrée).
 */
return [
    // ── praticiens ──
    ListerPraticiensAction::class => function (ContainerInterface $c) {
        return new ListerPraticiensAction($c->get(PraticienServiceInterface::class));
    },
    GetPraticienAction::class => function (ContainerInterface $c) {
        return new GetPraticienAction($c->get(PraticienServiceInterface::class));
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
];
