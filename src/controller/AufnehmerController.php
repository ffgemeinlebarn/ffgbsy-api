<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\AufnehmerService;

final class AufnehmerController extends BaseController
{
    private AufnehmerService $aufnehmerService;

    public function __construct(ContainerInterface $container)
    {
        $this->aufnehmerService = $container->get('aufnehmer');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->aufnehmerService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->aufnehmerService->read();
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->aufnehmerService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->aufnehmerService->update($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->aufnehmerService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
