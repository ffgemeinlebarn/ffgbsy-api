<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\BestellbonsService;

final class BestellbonsController extends BaseController
{
    private BestellbonsService $bestellbonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->bestellbonsService = $container->get('bestellbons');
    }

    public function read(Request $request, Response $response, $args): Response
    {
        $data = $this->bestellbonsService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function readByBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->bestellbonsService->readByBestellung($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function printSingle(Request $request, Response $response, $args): Response
    {
        $data = $this->bestellbonsService->printSingle($request->getParsedBody());//(array) $this->bestellbonsService->read($args['id']));
        return $this->responseAsJson($response, $data);
    }

    public function printMultiple(Request $request, Response $response): Response
    {
        $data = $this->bestellbonsService->printMultiple($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }
}
