<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\AufnehmerService;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\TischkategorienService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\ProduktbereicheService;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProdukteService;

final class DatenController extends BaseController
{
    private AufnehmerService $aufnehmerService;
    private TischeService $tischeService;
    private TischkategorienService $tischkategorienService;
    private ProdukteinteilungenService $produkteinteilungenService;
    private ProduktbereicheService $produktbereicheService;
    private ProduktkategorienService $produktkategorienService;
    private ProdukteService $produkteService;
    private $database;
    private string $databaseName;

    public function __construct(ContainerInterface $container)
    {
        $this->aufnehmerService = $container->get('aufnehmer');
        $this->tischeService = $container->get('tische');
        $this->tischkategorienService = $container->get('tischkategorien');
        $this->produkteinteilungenService = $container->get('produkteinteilungen');
        $this->produktbereicheService = $container->get('produktbereiche');
        $this->produktkategorienService = $container->get('produktkategorien');
        $this->produkteService = $container->get('produkte');
        $this->database = $container->get('database');
        $this->databaseName = $container->get('settings')['database']['database'];
    }

    public function latest(Request $request, Response $response): Response
    {
        $data = new \stdClass();

        $data->aufnehmer = $this->aufnehmerService->read();
        $data->tische = $this->tischeService->read();
        $data->tischkategorien = $this->tischkategorienService->read();
        $data->produkteinteilungen = $this->produkteinteilungenService->read();
        $data->produktbereiche = $this->produktbereicheService->read();
        $data->produktkategorien = $this->produktkategorienService->readAllNested();
        $data->produkte = $this->produkteService->read();
        
        $sth = $this->database->prepare(
            "SELECT 
                UPDATE_TIME
            FROM   
                information_schema.tables
            WHERE
                TABLE_SCHEMA = '$this->databaseName'
            AND (TABLE_NAME = 'aufnehmer' OR 
                TABLE_NAME = 'drucker' OR 
                TABLE_NAME = 'eigenschaften' OR 
                TABLE_NAME = 'geraete' OR 
                TABLE_NAME = 'produktbreiche' OR 
                TABLE_NAME = 'produkte' OR 
                TABLE_NAME = 'produkteinteilungen' OR 
                TABLE_NAME = 'produkte_eigenschaften' OR 
                TABLE_NAME = 'produktkategorien' OR 
                TABLE_NAME = 'produktkategorien_eigenschaften' OR 
                TABLE_NAME = 'tische' OR 
                TABLE_NAME = 'tischkategorien')
            ORDER BY 
                UPDATE_TIME DESC
            LIMIT 1");
        $sth->execute();
        $datetime = new \DateTime($sth->fetch()['UPDATE_TIME'] ?? "now");
        $data->timestamp = $datetime->format(DATE_RFC3339);
        $data->version = $datetime->getTimestamp();

        return $this->responseAsJson($response, $data);
    }
}