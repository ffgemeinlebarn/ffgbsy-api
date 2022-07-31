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

    public function createBonsAndPrintBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->printService->createBonsAndPrintBestellung($args['id']);
        return $this->responseAsJson($response, $data);
    }
}