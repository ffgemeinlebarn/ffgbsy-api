<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\NotificationsService;

final class NotificationsController extends BaseController
{
    private NotificationsService $notificationsService;

    public function __construct(ContainerInterface $container)
    {
        $this->notificationsService = $container->get('notifications');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->notificationsService->create($request->getParsedBody());
        return $this->responseAsJson($response, $data);
    }

    public function readSingle(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->notificationsService->read($args['id']);
        return $this->responseAsJson($response, $data);
    }

    public function readUntil(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->notificationsService->readUntil($args['until']);
        return $this->responseAsJson($response, $data);
    }

    public function readSince(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $data = $this->notificationsService->readSince($args['since']);
        return $this->responseAsJson($response, $data);
    }
}
