<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\SetupService;

final class SetupController extends BaseController
{
    private SetupService $setupService;

    public function __construct(ContainerInterface $container)
    {
        $this->setupService = $container->get('setup');
    }

    public function setupDatabase(Request $request, Response $response): Response
    {
        return $this->responseAsJson($response, [
            "result" => $this->setupService->setupDatabase()
        ]);
    }

    public function seedData(Request $request, Response $response): Response
    {
        return $this->responseAsJson($response, [
            "result" => $this->setupService->seedData()
        ]);
    }
}
