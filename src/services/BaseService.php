<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use PDO;

abstract class BaseService
{
    protected $db = null;
    protected $logger = null;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->db = $container->get('database');
        $this->logger = $logger;
    }

    final protected function singleRead($sth)
    {
        if ($sth->execute()) {
            if ($data = $sth->fetch(PDO::FETCH_OBJ)) {
                return $this->singleMap($data);
            }
        } else {
            // sonarlint
        }
    }

    final protected function multiRead($sth)
    {
        if ($sth->execute()) {
            $arr = [];
            foreach ($sth->fetchAll(PDO::FETCH_OBJ) as $item) {
                array_push($arr, $this->singleMap($item));
            }

            return $arr;
        } else {
            // sonarlint
        }
    }

    final protected function asNumber($str)
    {
        return intval($str);
    }

    final protected function asDecimal($str)
    {
        return floatval($str);
    }

    final protected function asNumberOrNull($str)
    {
        return $str == null ? null : intval($str);
    }

    final protected function asBool($str)
    {
        return filter_var($str, FILTER_VALIDATE_BOOLEAN);
    }

    final protected function asIsoTimestamp($str)
    {
        return date(DATE_ISO8601, strtotime($str));
    }
}
