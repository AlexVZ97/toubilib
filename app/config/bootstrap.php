<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\persistence\PgRendezVousRepository;
use toubilib\application\usecases\RendezVousService;

// ── variables de configuration (fichier config/.env) ──
Dotenv\Dotenv::createImmutable(__DIR__, '.env')->safeLoad();

// ── assemblage des objets : injection de dépendances "à la main" ──
//    (TD1.2 : tout ceci sera déplacé dans le conteneur PHP-DI)
$pdo = new PDO(
    "{$_ENV['lib.driver']}:host={$_ENV['lib.host']};dbname={$_ENV['lib.database']}",
    $_ENV['lib.username'],
    $_ENV['lib.password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$rdvRepository = new PgRendezVousRepository($pdo);          // adaptateur de sortie
$rdvService    = new RendezVousService($rdvRepository);    // noyau : reçoit l'interface
$annulerAction = new AnnulerRendezVousAction($rdvService); // adaptateur d'entrée

// ── application Slim ──
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');   // erreurs toujours en JSON

// ── routes ──
$app->post('/rdvs/{id}/annuler', $annulerAction)->setName('rdv-annuler');

return $app;
