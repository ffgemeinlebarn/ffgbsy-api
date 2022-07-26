<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProduktbereicheService;

final class ProduktbereicheController extends BaseController
{
    private ProduktbereicheService $produktbereicheService;

    public function __construct(ContainerInterface $container)
    {
        $this->produktbereicheService = $container->get('produktbereiche');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->produktbereicheService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produktbereicheService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produktbereicheService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->produktbereicheService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->produktbereicheService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
