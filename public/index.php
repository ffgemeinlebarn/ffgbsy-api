<?php

declare(strict_types=1);

use FFGBSY\Application\ResponseEmitter;
use FFGBSY\Application\HttpErrorHandler;
use FFGBSY\Services\AufnehmerService;
use FFGBSY\Services\TischkategorienService;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\GrundprodukteService;
use FFGBSY\Services\ProduktbereicheService;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProdukteService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\EigenschaftenService;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\ConstantsService;
use FFGBSY\Services\PrintService;
use FFGBSY\Services\PrintBonsService;
use FFGBSY\Services\BonsService;
use FFGBSY\Services\BonsDruckService;
use FFGBSY\Services\StatistikenService;
use FFGBSY\Services\CelebrationService;
use FFGBSY\Services\NotificationsService;
use FFGBSY\Services\LogsService;
use FFGBSY\Services\SetupService;
use FFGBSY\Services\AdminNotificationsService;

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Psr\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

/**********************************************************
 *** DI Container
 **********************************************************/

$containerBuilder = new ContainerBuilder();

// Should be set to true in production
if (false) {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

$settings = require_once __DIR__ . '/../settings.php';
$database = require_once __DIR__ . '/../src/builder/database.php';

$settings($containerBuilder);
$database($containerBuilder);

$containerBuilder->addDefinitions([
    'adminNotifications' => fn (ContainerInterface $c, LoggerInterface $logger) => new AdminNotificationsService($c, $logger),
    'aufnehmer' => fn (ContainerInterface $c, LoggerInterface $logger) => new AufnehmerService($c, $logger),
    'tischkategorien' => fn (ContainerInterface $c, LoggerInterface $logger) => new TischkategorienService($c, $logger),
    'tische' => fn (ContainerInterface $c, LoggerInterface $logger) => new TischeService($c, $logger),
    'drucker' => fn (ContainerInterface $c, LoggerInterface $logger) => new DruckerService($c, $logger),
    'grundprodukte' => fn (ContainerInterface $c, LoggerInterface $logger) => new GrundprodukteService($c, $logger),
    'produktbereiche' => fn (ContainerInterface $c, LoggerInterface $logger) => new ProduktbereicheService($c, $logger),
    'produktkategorien' => fn (ContainerInterface $c, LoggerInterface $logger) => new ProduktkategorienService($c, $logger),
    'produkteinteilungen' => fn (ContainerInterface $c, LoggerInterface $logger) => new ProdukteinteilungenService($c, $logger),
    'produkte' => fn (ContainerInterface $c, LoggerInterface $logger) => new ProdukteService($c, $logger),
    'eigenschaften' => fn (ContainerInterface $c, LoggerInterface $logger) => new EigenschaftenService($c, $logger),
    'bestellungen' => fn (ContainerInterface $c, LoggerInterface $logger) => new BestellungenService($c, $logger),
    'bestellpositionen' => fn (ContainerInterface $c, LoggerInterface $logger) => new BestellpositionenService($c, $logger),
    'bons' => fn (ContainerInterface $c, LoggerInterface $logger) => new BonsService($c, $logger),
    'bonsDruck' => fn (ContainerInterface $c, LoggerInterface $logger) => new BonsDruckService($c, $logger),
    'constants' => fn (ContainerInterface $c, LoggerInterface $logger) => new ConstantsService($c, $logger),
    'print' => fn (ContainerInterface $c, LoggerInterface $logger) => new PrintService($c, $logger),
    'printBons' => fn (ContainerInterface $c, LoggerInterface $logger) => new PrintBonsService($c, $logger),
    'statistiken' => fn (ContainerInterface $c, LoggerInterface $logger) => new StatistikenService($c, $logger),
    'celebration' => fn (ContainerInterface $c, LoggerInterface $logger) => new CelebrationService($c, $logger),
    'notifications' => fn (ContainerInterface $c, LoggerInterface $logger) => new NotificationsService($c, $logger),
    'logs' => fn (ContainerInterface $c, LoggerInterface $logger) => new LogsService($c, $logger),
    'setup' => fn (ContainerInterface $c, LoggerInterface $logger) => new SetupService($c, $logger)
]);

$containerBuilder->addDefinitions([
    LoggerInterface::class => function () {
        $logger = new Logger('ffgbsy');
        $streamHandler = new StreamHandler('/var/log/ffgbsy/error.log', 100);
        $logger->pushHandler($streamHandler);

        return $logger;
    },
]);

$container = $containerBuilder->build();

/**********************************************************
 *** App Instance
 **********************************************************/

AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

/**********************************************************
 *** Routes
 **********************************************************/

$routes = require __DIR__ . '/../src/builder/routes.php';
$routes($app);

/**********************************************************
 *** Middlewares
 **********************************************************/

$settings = $container->get('settings');


$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Error Middleware & Logging
$logger = new Logger('ffgbsy');
$streamHandler = new StreamHandler('/var/log/ffgbsy/error.log', 100);
$logger->pushHandler($streamHandler);

$errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
$errorMiddleware->setDefaultErrorHandler($errorHandler);
// $errorHandler = $errorMiddleware->getDefaultErrorHandler();
// $errorHandler->forceContentType('application/json');

// Run App & Emit Response
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
