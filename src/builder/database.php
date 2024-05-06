<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'database' => function (ContainerInterface $c) {
            $settings = $c->get('settings')['database'];

            $dsn = "mysql:dbname=" . $settings['database'] . ";host=" . $settings['host'] . ";charset=utf8";
            $pdo = new PDO($dsn, $settings['username'], $settings['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        }
    ]);
};
