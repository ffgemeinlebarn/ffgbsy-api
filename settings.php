<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions([
        'settings' => function (ContainerInterface $c) {
            return [
                'environment' => getenv('ENV'),
                'database' => [
                    'host' => getenv('DB_HOST'),
                    'database' => getenv('DB_DATABASE'),
                    'username' => getenv('DB_USERNAME'),
                    'password' => getenv('DB_PASSWORD')
                ]
            ];
        }
    ]);
};
