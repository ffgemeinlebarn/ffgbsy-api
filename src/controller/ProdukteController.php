<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use FFGBSY\Services\DruckerService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProdukteService;
use FFGBSY\Services\ProduktkategorienService;
use FFGBSY\Services\ProduktbereicheService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\GrundprodukteService;
use FFGBSY\Services\EigenschaftenService;

final class ProdukteController extends BaseController
{
    private ProdukteService $produkteService;
    private ProduktkategorienService $produktkategorienService;
    private ProduktbereicheService $produktbereicheService;
    private ProdukteinteilungenService $produkteinteilungenService;
    private DruckerService $druckerService;
    private GrundprodukteService $grundprodukteService;
    private EigenschaftenService $eigenschaftenService;

    public function __construct(ContainerInterface $container)
    {
        $this->produkteService = $container->get('produkte');
        $this->produktbereicheService = $container->get('produktbereiche');
        $this->produktkategorienService = $container->get('produktkategorien');
        $this->produkteinteilungenService = $container->get('produkteinteilungen');
        $this->druckerService = $container->get('drucker');
        $this->grundprodukteService = $container->get('grundprodukte');
        $this->eigenschaftenService = $container->get('eigenschaften');
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

        foreach ($data as $item) {
            $item->produkteinteilung = $this->produkteinteilungenService->read($item->produkteinteilungen_id);
            $item->produkteinteilung->produktkategorie = $this->produktkategorienService->read($item->produkteinteilung->produktkategorien_id);
            $item->grundprodukt = $item->grundprodukte_id != null ? $this->grundprodukteService->read($item->grundprodukte_id) : null;
        }

        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteService->read($args['id']);
        $data->drucker = $data->drucker_id_level_2 ? $this->druckerService->read($data->drucker_id_level_2) : null;
        $data->produkteinteilung = $this->produkteinteilungenService->read($data->produkteinteilungen_id);
        $data->produkteinteilung->produktkategorie = $this->produktkategorienService->read($data->produkteinteilung->produktkategorien_id);
        $data->produkteinteilung->produktkategorie->drucker = $data->produkteinteilung->produktkategorie->drucker_id_level_1 ? $this->druckerService->read($data->produkteinteilung->produktkategorie->drucker_id_level_1) : null;
        $data->produkteinteilung->produktkategorie->eigenschaften = $this->eigenschaftenService->readAllByProduktkategorie($data->produkteinteilung->produktkategorien_id);
        $data->produkteinteilung->produktkategorie->produktbereich = $this->produktbereicheService->read($data->produkteinteilung->produktkategorie->produktbereiche_id);
        $data->produkteinteilung->produktkategorie->produktbereich->drucker = $data->produkteinteilung->produktkategorie->produktbereich->drucker_id_level_0 ? $this->produktbereicheService->read($data->produkteinteilung->produktkategorie->produktbereich->drucker_id_level_0) : null;
        $data->grundprodukt = $data->grundprodukte_id != null ? $this->grundprodukteService->read($data->grundprodukte_id) : null;
        $data->eigenschaften = $this->eigenschaftenService->readAllByProdukt($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $produkt = $this->produkteService->update($request->getParsedBody());

        $data = $this->produkteService->read($produkt->id);
        $data->produkteinteilung = $this->produkteinteilungenService->read($data->produkteinteilungen_id);
        $data->produkteinteilung->produktkategorie = $this->produktkategorienService->read($data->produkteinteilung->produktkategorien_id);
        $data->produkteinteilung->produktkategorie->eigenschaften = $this->eigenschaftenService->readAllByProduktkategorie($data->produkteinteilung->produktkategorien_id);
        $data->grundprodukt = $data->grundprodukte_id != null ? $this->grundprodukteService->read($data->grundprodukte_id) : null;
        $data->eigenschaften = $this->eigenschaftenService->readAllByProdukt($data->id);
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produkteService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
