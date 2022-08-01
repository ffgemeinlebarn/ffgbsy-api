<?php

    declare(strict_types=1);

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;

    return function (ContainerBuilder $containerBuilder) 
    {

        $containerBuilder->addDefinitions([
            'settings' => function (ContainerInterface $c)
            {
                return [
                    'database' => [
                        'host' => 'localhost',
                        'database' => 'ffgbsy',
                        'username' => 'root',
                        'password' => ''
                    ]
                ];
            }
        ]);
    };