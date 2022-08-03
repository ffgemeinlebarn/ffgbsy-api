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
use FFGBSY\Controller\PrintController;
use FFGBSY\Controller\DatenController;
use FFGBSY\Controller\StatusController;
use FFGBSY\Controller\BonsController;
use FFGBSY\Controller\StatistikenController;

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
        $group->post('/{bestellungen_id}/bestellpositionen/{bestellpositionen_id}', "$controller:stornoBestellposition");
    });

    $app->group('/bons', function (Group $group)
    {
        $controller = BonsController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_ID, "$controller:read");
        $group->get('/bestellungen/{id}/{type}', "$controller:readByTypeAndBestellung");
        $group->post('/druck', "$controller:printMultiple");
        $group->post('/{id}/druck', "$controller:printSingle");
    });

    $app->group('/daten', function (Group $group)
    {
        $controller = DatenController::class;
        $group->get('/latest', "$controller:latest");
    });

    $app->group('/status', function (Group $group)
    {
        $controller = StatusController::class;
        $group->get('/systemstatus', "$controller:systemstatus");
    });

    $app->group('/statistiken', function (Group $group)
    {
        $controller = StatistikenController::class;
        $group->get('/timeline', "$controller:timeline");
        $group->get('/kennzahlen', "$controller:kennzahlen");
    });
};
