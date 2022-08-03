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

    public function read(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function readByTypeAndBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->readByTypeAndBestellung($args['type'], $args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function printSingle(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->printSingle($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function printMultiple(Request $request, Response $response): Response
    {
        $data = $this->bonsService->printMultiple($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }
}
