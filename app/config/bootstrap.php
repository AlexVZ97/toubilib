<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use toubilib\adapters\controllers\handlers\LoggingErrorHandler;
use toubilib\adapters\controllers\middlewares\Cors;

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
$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), true, false);
$errorHandler = new LoggingErrorHandler(
    $app->getCallableResolver(), $app->getResponseFactory(), $container->get(LoggerInterface::class)
);
$errorHandler->forceContentType('application/json');
$errorMiddleware->setDefaultErrorHandler($errorHandler);
$app->add(Cors::class);

(require __DIR__ . '/routes.php')($app);

return $app;
