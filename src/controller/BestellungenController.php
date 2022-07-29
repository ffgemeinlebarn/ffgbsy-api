<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\GrundprodukteService;

final class BestellungenController extends BaseController
{
    private BestellungenService $bestellungenService;
    private GrundprodukteService $grundprodukteService;

    public function __construct(ContainerInterface $container)
    {
        $this->bestellungenService = $container->get('bestellungen');
        $this->grundprodukteService = $container->get('grundprodukte');
    }

    public function create(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $params = $request->getServerParams();
        $input['ip'] = $params['REMOTE_ADDR'] ?? null;
        
        // 1. Check Grundprodukte
        if ($notAvailabile = $this->bestellungenService->checkAvailabilityForBestellpositionen($input['bestellpositionen']))
        {
            return $this->responseAsJson($response, $notAvailabile);
        }
        
        // 2. Create Bestellung
        $data = $this->bestellungenService->create($input);
        
        // 3. Count down Grundprodukte
        

        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->bestellungenService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->bestellungenService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
