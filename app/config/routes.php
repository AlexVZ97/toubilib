<?php
declare(strict_types=1);

use Slim\App;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

return function (App $app): void {
    $app->post('/rdvs/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');
};
