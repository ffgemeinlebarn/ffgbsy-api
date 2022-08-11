<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\LogsService;

final class LogsController extends BaseController
{
    private LogsService $logsService;

    public function __construct(ContainerInterface $container)
    {
        $this->logsService = $container->get('logs');
    }

    public function create(Request $request, Response $response): Response
    {
        $params = $request->getServerParams();
        $input = $request->getParsedBody();
        $input['device_ip'] = $params['REMOTE_ADDR'] ?? '';
        $input['additional'] = json_encode($input['additional']);

        $data = $this->logsService->create($input);
        return $this->responseAsJson($response, $data);
    }

    public function read(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->logsService->read();
        return $this->responseAsJson($response, $data);
    }
}
