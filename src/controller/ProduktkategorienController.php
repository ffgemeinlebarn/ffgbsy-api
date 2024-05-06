<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProduktbereicheService;
use FFGBSY\Services\EigenschaftenService;

final class ProduktkategorienController extends BaseController
{
    private ProduktkategorienService $produktkategorienService;
    private ProduktbereicheService $produktbereicheService;
    private EigenschaftenService $eigenschaftenService;

    public function __construct(ContainerInterface $container)
    {
        $this->produktkategorienService = $container->get('produktkategorien');
        $this->produktbereicheService = $container->get('produktbereiche');
        $this->eigenschaftenService = $container->get('eigenschaften');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->produktkategorienService->create($request->getParsedBody());
        $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produktkategorienService->read();

        foreach($data as $item)
        {
            $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        }

        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produktkategorienService->read($args['id']);
        $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        $data->eigenschaften = $this->eigenschaftenService->readAllByProduktkategorie($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produktkategorienService->update($request->getParsedBody());
        $data->produktbereich = $this->produktbereicheService->read($data->produktbereiche_id);
        $data->eigenschaften = $this->eigenschaftenService->readAllByProduktkategorie($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produktkategorienService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
