<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\application\ports\api\RendezVousServiceInterface;

/*
 * API : les recettes pour fabriquer les actions Slim (adaptateurs d'entrée).
 */
return [
    AnnulerRendezVousAction::class => function (ContainerInterface $c) {
        return new AnnulerRendezVousAction($c->get(RendezVousServiceInterface::class));
    },
];
