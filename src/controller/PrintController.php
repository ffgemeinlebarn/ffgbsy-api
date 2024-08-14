<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\BonsDruckService;
use FFGBSY\Services\PrintBonsService;

final class PrintController extends BaseController
{
    private PrintBonsService $printBonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->printBonsService = $container->get('printBons');
    }

    public function printSingleBon(Request $request, Response $response, $args): Response
    {
        $data = $this->printBonsService->printSingleBonById($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function printMultipleBons(Request $request, Response $response, $args): Response
    {
        $body = $request->getParsedBody();
        $data = $this->printBonsService->printMultipleBonsByIds($body);
        return $this->responseAsJson($response, $data);
    }

    public function printBestellung(Request $request, Response $response, $args): Response
    {
        $data = $this->printBonsService->printBestellbonsOfBestellungById($args['id']);
        return $this->responseAsJson($response, $data);
    }
}
