<?php
declare(strict_types=1);

use Slim\App;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;

/*
 * ROUTES : méthode HTTP + URI  →  action.
 * On donne le NOM de la classe : Slim demandera l'objet au conteneur.
 */
return function (App $app): void {

    // ── praticiens ──
    $app->get('/praticiens', ListerPraticiensAction::class)->setName('praticiens-liste');
    $app->get('/praticiens/{id}', GetPraticienAction::class)->setName('praticien-detail');

    // ── rendez-vous ──
    $app->get('/rdvs/{id}', GetRendezVousAction::class)->setName('rdv-detail');
    $app->post('/rdvs/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');

};
