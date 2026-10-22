<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

date_default_timezone_set('Europe/Paris');

Dotenv\Dotenv::createImmutable(__DIR__, '.env')->safeLoad();

$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->addDefinitions(__DIR__ . '/settings.php');
$builder->addDefinitions(__DIR__ . '/services.php');
$builder->addDefinitions(__DIR__ . '/api.php');
$container = $builder->build();

$app = AppFactory::createFromContainer($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), false, false);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

(require __DIR__ . '/routes.php')($app);

return $app;
