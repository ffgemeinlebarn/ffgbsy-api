<?php

    declare(strict_types=1);
   
    use FFGBSY\Application\ResponseEmitter\ResponseEmitter;
    use FFGBSY\Services\AufnehmerService;
    use FFGBSY\Services\TischkategorienService;

    use DI\ContainerBuilder;
    use Slim\Factory\AppFactory;
    use Slim\Factory\ServerRequestCreatorFactory;
    use Psr\Container\ContainerInterface;

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

    $settings = require __DIR__ . '/../src/builder/settings.php';
    $database = require __DIR__ . '/../src/builder/database.php';

    $settings($containerBuilder);
    $database($containerBuilder);

    $containerBuilder->addDefinitions([
        'aufnehmer' => fn (ContainerInterface $c) => new AufnehmerService($c),
        'tischkategorien' => fn (ContainerInterface $c) => new TischkategorienService($c)
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

    $displayErrorDetails = $settings['displayErrorDetails'];
    $logError = $settings['logError'];
    $logErrorDetails = $settings['logErrorDetails'];

    // Add Body Parsing Middleware
    $app->addBodyParsingMiddleware();

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Run App & Emit Response
    $serverRequestCreator = ServerRequestCreatorFactory::create();
    $request = $serverRequestCreator->createServerRequestFromGlobals();

    // $response = $app->handle($request);
    // $responseEmitter = new ResponseEmitter();
    // $responseEmitter->emit($response);

    $app->run();