<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Controller\AufnehmerController;

return function (App $app) 
{
    $app->options('/{routes:.*}', function (Request $request, Response $response)
    {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response)
    {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/aufnehmer', function (Group $group)
    {
        $controller = AufnehmerController::class;
        $group->post('', "$controller:create");
        $group->get('', "$controller:readAll");
        $group->get('/{id}', "$controller:readSingle");
        $group->put('/{id}', "$controller:update");
        $group->delete('/{id}', "$controller:delete");
    });
};
