<?php
declare(strict_types=1);

use Slim\App;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;

return function (App $app): void {
    $app->get('/praticiens', ListerPraticiensAction::class)->setName('praticiens-liste');
    $app->get('/praticiens/{id}', GetPraticienAction::class)->setName('praticien-detail');

    $app->post('/rdvs', CreerRendezVousAction::class)->setName('rdv-creer');
    $app->get('/rdvs/{id}', GetRendezVousAction::class)->setName('rdv-detail');
    $app->post('/rdvs/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');
};
