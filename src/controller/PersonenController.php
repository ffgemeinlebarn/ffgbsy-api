<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\PersonenService;

final class PersonenController extends BaseController
{
    private PersonenService $personenService;

    public function __construct(ContainerInterface $container)
    {
        $this->personenService = $container->get('personen');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->personenService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->personenService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->personenService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->personenService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->personenService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
