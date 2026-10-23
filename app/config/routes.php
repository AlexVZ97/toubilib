<?php
declare(strict_types=1);

use Slim\App;
use toubilib\adapters\controllers\actions\AgendaPraticienAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\CreneauxPraticienAction;
use toubilib\adapters\controllers\actions\GetPatientAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\HistoriquePatientAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;

/*
 * ROUTES : méthode HTTP + URI  →  action.
 * On donne le NOM de la classe : Slim demandera l'objet au conteneur.
 * Le nom de route (setName) sert à construire les liens HATEOAS.
 */
return function (App $app): void {

    // ── praticiens ──
    $app->get('/praticiens', ListerPraticiensAction::class)->setName('praticiens-liste');      // + ?specialite=&ville=
    $app->get('/praticiens/{id}', GetPraticienAction::class)->setName('praticien-detail');
    $app->get('/praticiens/{id}/rdvs', AgendaPraticienAction::class)->setName('praticien-agenda');
    $app->get('/praticiens/{id}/creneaux', CreneauxPraticienAction::class)->setName('praticien-creneaux');

    // ── patients ──
    $app->get('/patients/{id}', GetPatientAction::class)->setName('patient-detail');
    $app->get('/patients/{id}/rdvs', HistoriquePatientAction::class)->setName('patient-historique');

    // ── rendez-vous ──
    $app->post('/rdvs', CreerRendezVousAction::class)->setName('rdv-creer');
    $app->get('/rdvs/{id}', GetRendezVousAction::class)->setName('rdv-detail');
    $app->post('/rdvs/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');
    $app->post('/rdvs/{id}/honorer', HonorerRendezVousAction::class)->setName('rdv-honorer');
    $app->post('/rdvs/{id}/ignorer', IgnorerRendezVousAction::class)->setName('rdv-ignorer');

};
