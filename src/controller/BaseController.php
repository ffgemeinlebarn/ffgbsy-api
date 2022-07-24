<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Http\Message\ResponseInterface as Response;

abstract class BaseController 
{
    public function responseAsJson(
        Response $response,
        $data,
        int $status = 200,
        int $encodingOptions = 0
    ): Response {
        $json = json_encode($data, $encodingOptions);

        if ($json === false) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        $response->getBody()->write($json);

        $responseWithJson = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }

        return $responseWithJson;
    }
}