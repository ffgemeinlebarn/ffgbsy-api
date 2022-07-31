<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\GrundprodukteService;

final class StatusController extends BaseController
{
    private DruckerService $druckerService;
    private GrundprodukteService $grundprodukteService;

    public function __construct(ContainerInterface $container)
    {
        $this->druckerService = $container->get('drucker');
        $this->grundprodukteService = $container->get('grundprodukte');
    }

    public function systemstatus(Request $request, Response $response): Response
    {
        return $this->responseAsJson($response, [
            "api" => true,
            "drucker" => $this->druckerService->checkConnections(),
            "grundprodukte" => $this->grundprodukteService->read()
        ]);
    }
}