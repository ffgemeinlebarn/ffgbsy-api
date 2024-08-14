<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\BonsService;

final class BonsController extends BaseController
{
    private BonsService $bonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->bonsService = $container->get('bons');
    }

    public function create(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function read(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->read(null, $request->getQueryParams());
        return $this->responseAsJson($response, $data);
    }

    public function readByTypeAndBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->readByTypeAndBestellung($args['type'], $args['id']);
        return $this->responseAsJson($response, $data);
    }
}
