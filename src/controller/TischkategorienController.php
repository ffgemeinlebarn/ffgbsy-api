<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\TischkategorienService;

final class TischkategorienController extends BaseController
{
    private TischkategorienService $tischkategorienService;

    public function __construct(ContainerInterface $container)
    {
        $this->tischkategorienService = $container->get('tischkategorien');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->tischkategorienService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->tischkategorienService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->tischkategorienService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->tischkategorienService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->tischkategorienService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
