<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProdukteService;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\GrundprodukteService;

final class ProdukteController extends BaseController
{
    private ProdukteService $produkteService;
    private ProduktkategorienService $produktkategorienService;
    private ProdukteinteilungenService $produkteinteilungenService;
    private GrundprodukteService $grundprodukteService;

    public function __construct(ContainerInterface $container)
    {
        $this->produkteService = $container->get('produkte');
        $this->produktkategorienService = $container->get('produktkategorien');
        $this->produkteinteilungenService = $container->get('produkteinteilungen');
        $this->grundprodukteService = $container->get('grundprodukte');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->produkteService->create($request->getParsedBody());
        $data->produktkategorie = $this->produktkategorienService->read($data->produktkategorien_id);
        $data->produkteinteilung = $this->produkteinteilungenService->read($data->produkteinteilungen_id);
        $data->grundprodukt = $data->grundprodukte_id != null ? $this->grundprodukteService->read($data->grundprodukte_id) : null;
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produkteService->read();
        
        foreach($data as $item)
        {
            $item->produktkategorie = $this->produktkategorienService->read($item->produktkategorien_id);
            $item->produkteinteilung = $this->produkteinteilungenService->read($item->produkteinteilungen_id);
            $item->grundprodukt = $item->grundprodukte_id != null ? $this->grundprodukteService->read($item->grundprodukte_id) : null;
        }

        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteService->read($args['id']);
        $data->produktkategorie = $this->produktkategorienService->read($data->produktkategorien_id);
        $data->produkteinteilung = $this->produkteinteilungenService->read($data->produkteinteilungen_id);
        $data->grundprodukt = $data->grundprodukte_id != null ? $this->grundprodukteService->read($data->grundprodukte_id) : null;
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produkteService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
