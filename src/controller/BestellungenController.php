<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Application\Exceptions\HttpBadRequestException;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\GrundprodukteService;
use FFGBSY\Services\PrintService;
use FFGBSY\Services\BonsService;

final class BestellungenController extends BaseController
{
    private BestellungenService $bestellungenService;
    private BestellpositionenService $bestellpositionenService;
    private GrundprodukteService $grundprodukteService;
    private PrintService $printService;
    private BonsService $bonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->bestellungenService = $container->get('bestellungen');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->grundprodukteService = $container->get('grundprodukte');
        $this->printService = $container->get('print');
        $this->bonsService = $container->get('bons');
    }

    public function checkAvailability(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        
        $data = $this->bestellungenService->checkAvailabilityForBestellpositionen($input['bestellpositionen']);
        
        return $this->responseAsJson($response, $data);
    }

    public function create(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $params = $request->getServerParams();
        $input['device_ip'] = $params['REMOTE_ADDR'] ?? null;
        
        // 1. Check Grundprodukte
        $availability = $this->bestellungenService->checkAvailabilityForBestellpositionen($input['bestellpositionen']);
        if (!$availability->success)
        {
            $err = new \stdClass();
            $err->statusCode = 400;
            $err->error = new \stdClass();
            $err->error->type = "BAD_REQUEST";
            $err->error->description = "AvailabilityCheck";
            $err->error->data = $availability;
            
            return $this->responseAsJson($response, $err, 400);
        }
        
        // 2. Create Bestellung
        $bestellung = $this->bestellungenService->create($input);

        // 3. Create Bons
        $affectedDruckerIds = $this->bonsService->getAffectedDruckerIdsForBestellung('bestellung', $bestellung->id);
        foreach($affectedDruckerIds as $druckerId)
        {
            $this->bonsService->create([
                "type" => "bestellung",
                "bestellungen_id" => $bestellung->id,
                "drucker_id" => $druckerId,
                "bestellpositionen" => $this->bestellpositionenService->readByTypeAndBestellungAndDrucker('bestellung', $bestellung->id, $druckerId)
            ]);
        }
        
        return $this->responseAsJson($response, $this->bestellungenService->read($bestellung->id));
    }

    public function stornoBestellposition(Request $request, Response $response, array $args): Response
    {
        $data = $this->bestellpositionenService->storno($args['bestellpositionen_id'], $request->getParsedBody()['anzahl']);
        return $this->responseAsJson($response, $data);
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
