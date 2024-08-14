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
use FFGBSY\Controller\ProdukteinteilungenController;
use FFGBSY\Controller\ProdukteController;
use FFGBSY\Controller\EigenschaftenController;
use FFGBSY\Controller\BestellungenController;
use FFGBSY\Controller\PrintController;
use FFGBSY\Controller\DatenController;
use FFGBSY\Controller\StatusController;
use FFGBSY\Controller\BonsController;
use FFGBSY\Controller\StatistikenController;
use FFGBSY\Controller\NotificationsController;
use FFGBSY\Controller\DebugController;
use FFGBSY\Controller\LogsController;
use FFGBSY\Controller\TestsController;
use FFGBSY\Controller\SetupController;

const PATH_ID    = '/{id}';
const PATH_EMPTY = '';

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $datetime = new \DateTime("now");
        $timestamp = $datetime->format(DATE_RFC3339);
        $response->getBody()->write($timestamp);
        return $response->withHeader('Content-Type', 'text/plain');
    });

    $app->group('/aufnehmer', function (Group $group) {
        $controller = AufnehmerController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/tischkategorien', function (Group $group) {
        $controller = TischkategorienController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/tische', function (Group $group) {
        $controller = TischeController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/drucker', function (Group $group) {
        $controller = DruckerController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/grundprodukte', function (Group $group) {
        $controller = GrundprodukteController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produktbereiche', function (Group $group) {
        $controller = ProduktbereicheController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produktkategorien', function (Group $group) {
        $controller = ProduktkategorienController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produkteinteilungen', function (Group $group) {
        $controller = ProdukteinteilungenController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/produkte', function (Group $group) {
        $controller = ProdukteController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/eigenschaften', function (Group $group) {
        $controller = EigenschaftenController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->put(PATH_ID, "$controller:update");
        $group->delete(PATH_ID, "$controller:delete");
    });

    $app->group('/bestellungen', function (Group $group) {
        $controller = BestellungenController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->post('/{bestellungen_id}/bestellpositionen/{bestellpositionen_id}', "$controller:stornoBestellposition");
    });

    $app->group('/bons', function (Group $group) {
        $controller = BonsController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:readAll");
        $group->get(PATH_ID, "$controller:read");
        $group->get('/bestellungen/{id}/{type}', "$controller:readByTypeAndBestellung");
    });

    $app->group('/print', function (Group $group) {
        $controller = PrintController::class;
        $group->post('/bons', "$controller:printMultipleBons");
        $group->post('/bons/{id}', "$controller:printSingleBon");
        $group->post('/bestellungen/{id}', "$controller:printBestellung");
    });

    $app->group('/daten', function (Group $group) {
        $controller = DatenController::class;
        $group->get('/latest', "$controller:latest");
    });

    $app->group('/status', function (Group $group) {
        $controller = StatusController::class;
        $group->get('/api', "$controller:api");
        $group->get('/drucker', "$controller:drucker");
        $group->get('/drucker/{id}', "$controller:druckerSingle");

        $group->get('/systemstatus', "$controller:systemstatus");
        $group->get('/phpinfo', "$controller:phpinfo");
    });

    $app->group('/statistiken', function (Group $group) {
        $controller = StatistikenController::class;
        $group->get('/timeline', "$controller:timeline");
        $group->get('/kennzahlen', "$controller:kennzahlen");
        $group->get('/produktbereiche', "$controller:produktbereiche");
        $group->get('/produktkategorien', "$controller:produktkategorien");
        $group->get('/produkte', "$controller:produkte");
    });

    $app->group('/notifications', function (Group $group) {
        $controller = NotificationsController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_ID, "$controller:readSingle");
        $group->get('/until/{until}', "$controller:readUntil");
        $group->get('/since/{since}', "$controller:readSince");
    });

    $app->group('/logs', function (Group $group) {
        $controller = LogsController::class;
        $group->post(PATH_EMPTY, "$controller:create");
        $group->get(PATH_EMPTY, "$controller:read");
    });

    $app->group('/setup', function (Group $group) {
        $controller = SetupController::class;
        $group->post('/database', "$controller:setupDatabase");
        $group->post('/seed', "$controller:seedData");
    });

    $app->group('/tests', function (Group $group) {
        $controller = TestsController::class;
        $group->post('/random-bestellung', "$controller:randomBestellung");
    });

    $app->group('/debug', function (Group $group) {
        $controller = DebugController::class;
        $group->get('/celebration', "$controller:celebration");
    });
};
