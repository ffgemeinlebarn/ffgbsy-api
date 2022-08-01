<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\StornobonsService;

final class StornobonsController extends BaseController
{
    private StornobonsService $stornobonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->stornobonsService = $container->get('stornobons');
    }

    public function create(Request $request, Response $response, $args): Response
    {
        $data = $this->stornobonsService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function read(Request $request, Response $response, $args): Response
    {
        $data = $this->stornobonsService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function readByBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->stornobonsService->readByBestellung($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function printSingle(Request $request, Response $response, $args): Response
    {
        $data = $this->stornobonsService->printSingle($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }
}
