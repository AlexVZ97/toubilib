<?php
declare(strict_types=1);

use Slim\App;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

/*
 * ROUTES : méthode HTTP + URI  →  action.
 * On donne le NOM de la classe : Slim demandera l'objet au conteneur.
 */
return function (App $app): void {

    $app->post('/rdvs/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');

};
