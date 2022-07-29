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

    public function createFromBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->bonsService->printBonsByBestellung($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
