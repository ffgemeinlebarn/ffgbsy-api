<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Controller\AufnehmerController;
use FFGBSY\Controller\TischkategorienController;
use FFGBSY\Controller\TischeController;
use FFGBSY\Controller\DruckerController;
use FFGBSY\Controller\GrundprodukteController;
use FFGBSY\Controller\ProduktbereicheController;
use FFGBSY\Controller\ProduktkategorienController;
use FFGBSY\Controller\ProdukteController;
use FFGBSY\Controller\EigenschaftenController;
use FFGBSY\Controller\BestellungenController;

const PATH_ID    = '/{id}';
const PATH_EMPTY = '';

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
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/tischkategorien', function (Group $group)
    {
        $controller = TischkategorienController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/tische', function (Group $group)
    {
        $controller = TischeController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/drucker', function (Group $group)
    {
        $controller = DruckerController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/grundprodukte', function (Group $group)
    {
        $controller = GrundprodukteController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produktbereiche', function (Group $group)
    {
        $controller = ProduktbereicheController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produktkategorien', function (Group $group)
    {
        $controller = ProduktkategorienController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produkte', function (Group $group)
    {
        $controller = ProdukteController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/eigenschaften', function (Group $group)
    {
        $controller = EigenschaftenController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/bestellungen', function (Group $group)
    {
        $controller = BestellungenController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
    });
};
