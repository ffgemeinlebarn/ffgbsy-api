<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\ProduktbereicheService;
use FFGBSY\Services\EigenschaftenService;

final class ProdukteinteilungenController extends BaseController
{
    private ProdukteinteilungenService $produkteinteilungenService;
    private ProduktkategorienService $produktkategorienService;
    private ProduktbereicheService $produktbereicheService;
    private EigenschaftenService $eigenschaftenService;

    public function __construct(ContainerInterface $container)
    {
        $this->produkteinteilungenService = $container->get('produkteinteilungen');
        $this->produktkategorienService = $container->get('produktkategorien');
        $this->produktbereicheService = $container->get('produktbereiche');
        $this->eigenschaftenService = $container->get('eigenschaften');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->produkteinteilungenService->create($request->getParsedBody());
        $data->produktkategorie = $this->produktkategorienService->read($data->produktkategorien_id);
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produkteinteilungenService->read();

        foreach ($data as $item) {
            $item->produktkategorie = $this->produktkategorienService->read($item->produktkategorien_id);
        }

        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteinteilungenService->read($args['id']);
        // $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        // $data->eigenschaften = $this->eigenschaftenService->readAllByProdukteinteilung($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produkteinteilungenService->update($request->getParsedBody());
        // $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        // $data->eigenschaften = $this->eigenschaftenService->readAllByProdukteinteilung($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteinteilungenService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
