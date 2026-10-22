<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

// ── 1. variables de configuration (fichier config/.env) ──
Dotenv\Dotenv::createImmutable(__DIR__, '.env')->safeLoad();

// ── 2. conteneur d'injection de dépendances (PHP-DI, SANS autowiring) ──
$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->addDefinitions(__DIR__ . '/settings.php');
$builder->addDefinitions(__DIR__ . '/services.php');
$builder->addDefinitions(__DIR__ . '/api.php');
$container = $builder->build();

// ── 3. application Slim qui utilise ce conteneur ──
$app = AppFactory::createFromContainer($container);

// ── 4. middlewares ──
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), false, false);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// ── 5. routes ──
(require __DIR__ . '/routes.php')($app);

return $app;
