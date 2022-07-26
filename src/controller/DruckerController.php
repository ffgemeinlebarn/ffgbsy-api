<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\DruckerService;

final class DruckerController extends BaseController
{
    private DruckerService $druckerService;

    public function __construct(ContainerInterface $container)
    {
        $this->druckerService = $container->get('drucker');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->druckerService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->druckerService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->druckerService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->druckerService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->druckerService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
