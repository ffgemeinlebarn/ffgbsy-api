<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\PrintService;
use FFGBSY\Services\ProdukteService;

final class DebugController extends BaseController
{
    private DruckerService $druckerService;
    private PrintService $printService;
    private ProdukteService $produkteService;

    public function __construct(ContainerInterface $container)
    {
        $this->druckerService = $container->get('drucker');
        $this->printService = $container->get('print');
        $this->produkteService = $container->get('produkte');
    }

    public function celebration(Request $request, Response $response): Response
    {
        $num = 1000;
        $produkt = $this->produkteService->read(114);
        $drucker = $this->druckerService->read(3);
        $setup = $this->printService->setupPrinter($drucker);

        if ($setup->success)
        {
            $printer = $setup->printer;
            $this->printService->printCelebrationHeader($printer);
            $this->printService->printCelebrationContent($printer, $num, "die", "Portion\n{$produkt->name}", "../assets/celebration-{$drucker->id}.png");
            $this->printService->printFinish($printer);
        }

        return $this->responseAsJson($response, null);
    }
}
