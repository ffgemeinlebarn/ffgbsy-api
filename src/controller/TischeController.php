<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\TischkategorienService;

final class TischeController extends BaseController
{
    private TischeService $tischeService;

    public function __construct(ContainerInterface $container)
    {
        $this->tischeService = $container->get('tische');
        $this->tischkategorienService = $container->get('tischkategorien');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->tischeService->create($request->getParsedBody());
        $data->tischkategorie = $this->tischkategorienService->read($data->tischkategorien_id);
        return $this->responseAsJson($response, $data);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->tischeService->read();

        foreach($data as $item)
        {
            $item->tischkategorie = $this->tischkategorienService->read($item->tischkategorien_id);
        }

        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->tischeService->read($args['id']);
        $data->tischkategorie = $this->tischkategorienService->read($data->tischkategorien_id);
        return $this->responseAsJson($response, $data);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->request = $request;
        $data = $this->tischeService->update($request->getParsedBody());
        $data->tischkategorie = $this->tischkategorienService->read($data->tischkategorien_id);
        return $this->responseAsJson($response, $data);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->tischeService->delete($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
