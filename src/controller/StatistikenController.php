<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\StatistikenService;

final class StatistikenController extends BaseController
{
    private StatistikenService $statistikenService;

    public function __construct(ContainerInterface $container)
    {
        $this->statistikenService = $container->get('statistiken');
    }

    public function timeline(Request $request, Response $response): Response
    {
        $data = $this->statistikenService->timeline();
        return $this->responseAsJson($response, $data);
    }

    public function kennzahlen(Request $request, Response $response): Response
    {
        $data = $this->statistikenService->kennzahlen();
        return $this->responseAsJson($response, $data);
    }

    public function produktbereiche(Request $request, Response $response): Response
    {
        $data = $this->statistikenService->produktbereiche();
        return $this->responseAsJson($response, $data);
    }

    public function produktkategorien(Request $request, Response $response): Response
    {
        $data = $this->statistikenService->produktkategorien();
        return $this->responseAsJson($response, $data);
    }
}
