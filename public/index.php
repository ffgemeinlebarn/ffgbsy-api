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
    use FFGBSY\Services\BonsService;
    use FFGBSY\Services\ConstantsService;
    use FFGBSY\Services\PrintService;
    use FFGBSY\Services\BestellbonsService;
    use FFGBSY\Services\BestellbonsDruckService;
    
    use DI\ContainerBuilder;
    use Slim\Factory\AppFactory;
    use Slim\Factory\ServerRequestCreatorFactory;
    use Psr\Container\ContainerInterface;
    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;

    require __DIR__ . '/../vendor/autoload.php';

    /**********************************************************
    *** DI Container
    **********************************************************/

    $containerBuilder = new ContainerBuilder();

    // Should be set to true in production
    if (false)
    {
        $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
    }

    $settings = require __DIR__ . '/../settings.php';
    $database = require __DIR__ . '/../src/builder/database.php';

    $settings($containerBuilder);
    $database($containerBuilder);

    $containerBuilder->addDefinitions([
        'aufnehmer' => fn (ContainerInterface $c) => new AufnehmerService($c),
        'tischkategorien' => fn (ContainerInterface $c) => new TischkategorienService($c),
        'tische' => fn (ContainerInterface $c) => new TischeService($c),
        'drucker' => fn (ContainerInterface $c) => new DruckerService($c),
        'grundprodukte' => fn (ContainerInterface $c) => new GrundprodukteService($c),
        'produktbereiche' => fn (ContainerInterface $c) => new ProduktbereicheService($c),
        'produktkategorien' => fn (ContainerInterface $c) => new ProduktkategorienService($c),
        'produkteinteilungen' => fn (ContainerInterface $c) => new ProdukteinteilungenService($c),
        'produkte' => fn (ContainerInterface $c) => new ProdukteService($c),
        'eigenschaften' => fn (ContainerInterface $c) => new EigenschaftenService($c),
        'bestellungen' => fn (ContainerInterface $c) => new BestellungenService($c),
        'bestellpositionen' => fn (ContainerInterface $c) => new BestellpositionenService($c),
        'bestellbons' => fn (ContainerInterface $c) => new BestellbonsService($c),
        'bestellbonsDruck' => fn (ContainerInterface $c) => new BestellbonsDruckService($c),
        'bons' => fn (ContainerInterface $c) => new BonsService($c),
        'constants' => fn (ContainerInterface $c) => new ConstantsService($c),
        'print' => fn (ContainerInterface $c) => new PrintService($c)
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
    $streamHandler = new StreamHandler(__DIR__ . '../../logs/error.log', 100);
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