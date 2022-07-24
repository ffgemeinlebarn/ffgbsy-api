<?php

    declare(strict_types=1);

    use DI\ContainerBuilder;
    use Monolog\Logger;
    use Psr\Container\ContainerInterface;

    return function (ContainerBuilder $containerBuilder) 
    {

        $containerBuilder->addDefinitions([
            'settings' => function (ContainerInterface $c)
            {
                return [
                    'displayErrorDetails' => true, // Should be set to false in production
                    'logError'            => true,
                    'logErrorDetails'     => true,
                    'logger' => [
                        'name' => 'slim-app',
                        'path' => __DIR__ . '/../logs/app.log',
                        'level' => Logger::DEBUG,
                    ],
                    'database' => [
                        'host' => 'localhost',
                        // 'port' => '3306',
                        'database' => 'ffgbsy_v1',
                        'username' => 'root',
                        'password' => ''
                    ]
                ];
            }
        ]);
    };