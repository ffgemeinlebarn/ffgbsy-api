<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Pimple\Psr11\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\CustomResponse as Response;
use Slim\Routing\RouteContext;
use PDO;

final class Bestellungen
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getBestellung(Request $request, Response $response): Response
    {
        $db = $this->container->get('db');

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $id = $route->getArgument('id');

        $sth  = $db->prepare("SELECT * from bestellungen WHERE id = :id");
        $sth->bindParam('id', $id, PDO::PARAM_INT);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);

        return $response->withJson($result);
    }
}
