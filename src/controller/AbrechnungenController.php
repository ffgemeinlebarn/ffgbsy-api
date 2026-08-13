<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\AbrechnungenService;
use FFGBSY\Services\PersonenService;
use Slim\Exception\HttpNotFoundException;

final class AbrechnungenController extends BaseController
{
    private AbrechnungenService $abrechnungenService;

    public function __construct(ContainerInterface $container)
    {
        $this->abrechnungenService = $container->get('abrechnungen');
    }

    public function readOverviewAll(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->abrechnungenService->readOverview($args['stelle']);

        return $this->responseAsJson($response, $data);
    }

    public function readOverviewSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->abrechnungenService->readOverview($args['stelle'], $args['id']);

        if ($data == null) {
            throw new HttpNotFoundException($request, "Person nicht gefunden!");
        }

        return $this->responseAsJson($response, $data);
    }

    public function createAbrechnung(Request $request, Response $response): Response
    {
        $data = $this->abrechnungenService->createAbrechnung($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function createRueckrechnung(Request $request, Response $response): Response
    {
        $data = $this->abrechnungenService->createRueckrechnung($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readKellnerStatus(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->abrechnungenService->readKellnerStatus($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function deleteAbrechnung(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->abrechnungenService->deleteAbrechnung($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function deleteRueckrechnung(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->abrechnungenService->deleteRueckrechnung($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
