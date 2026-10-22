<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\persistence\PgRendezVousRepository;
use toubilib\application\usecases\RendezVousService;

Dotenv\Dotenv::createImmutable(__DIR__, '.env')->safeLoad();

$pdo = new PDO(
    "{$_ENV['lib.driver']}:host={$_ENV['lib.host']};dbname={$_ENV['lib.database']}",
    $_ENV['lib.username'],
    $_ENV['lib.password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$rdvRepository = new PgRendezVousRepository($pdo);
$rdvService    = new RendezVousService($rdvRepository);
$annulerAction = new AnnulerRendezVousAction($rdvService);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$app->post('/rdvs/{id}/annuler', $annulerAction)->setName('rdv-annuler');

return $app;
