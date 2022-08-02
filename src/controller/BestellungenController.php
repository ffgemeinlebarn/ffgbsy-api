<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\GrundprodukteService;
use FFGBSY\Services\PrintService;
use FFGBSY\Services\BestellbonsService;

final class BestellungenController extends BaseController
{
    private BestellungenService $bestellungenService;
    private BestellpositionenService $bestellpositionenService;
    private GrundprodukteService $grundprodukteService;
    private PrintService $printService;
    private BestellbonsService $bestellbonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->bestellungenService = $container->get('bestellungen');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->grundprodukteService = $container->get('grundprodukte');
        $this->printService = $container->get('print');
        $this->bestellbonsService = $container->get('bestellbons');
    }

    public function create(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $params = $request->getServerParams();
        $input['device_ip'] = $params['REMOTE_ADDR'] ?? null;
        
        // 1. Check Grundprodukte
        if ($notAvailabile = $this->bestellungenService->checkAvailabilityForBestellpositionen($input['bestellpositionen']))
        {
            return $this->responseAsJson($response, $notAvailabile);
        }
        
        // 2. Create Bestellung
        $bestellung = $this->bestellungenService->create($input);

        // 3. Create Bestellbons
        $affectedDruckerIds = $this->bestellbonsService->getAffectedDruckerIdsForBestellung($bestellung->id);
        foreach($affectedDruckerIds as $druckerId)
        {
            $this->bestellbonsService->create([
                "bestellungen_id" => $bestellung->id,
                "drucker_id" => $druckerId,
                "bestellpositionen" => $this->bestellpositionenService->readByBestellungAndDrucker($bestellung->id, $druckerId)
            ]);
        }
        
        return $this->responseAsJson($response, $this->bestellungenService->read($bestellung->id));
    }

    public function readAll(Request $request, Response $response): Response
    {
        $data = $this->bestellungenService->read(null, $request->getQueryParams());
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $data = $this->bestellungenService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
