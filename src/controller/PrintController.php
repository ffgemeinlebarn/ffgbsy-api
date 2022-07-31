<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\PrintService;

final class PrintController extends BaseController
{
    private PrintService $printService;

    public function __construct(ContainerInterface $container)
    {
        $this->printService = $container->get('print');
    }

    public function printBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->printService->printBestellung($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function printBon(Request $request, Response $response, $args): Response
    {
        $data = $this->printService->printBon($args['bestellungen_id'], $args['drucker_id']);
        return $this->responseAsJson($response, $data);
    }
}