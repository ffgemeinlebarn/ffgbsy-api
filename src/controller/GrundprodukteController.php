<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\GrundprodukteService;

final class GrundprodukteController extends BaseController
{
    private GrundprodukteService $grundprodukteService;

    public function __construct(ContainerInterface $container)
    {
        $this->grundprodukteService = $container->get('grundprodukte');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->grundprodukteService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->grundprodukteService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->grundprodukteService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->grundprodukteService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->grundprodukteService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
