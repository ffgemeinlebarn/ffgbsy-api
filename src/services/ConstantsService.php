<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class ConstantsService extends BaseService
    {
        public function set($key, $value)
        {
            $sth = $this->db->prepare("INSERT INTO constants (name, value) VALUES (:name, :value)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':value', $data['value'], PDO::PARAM_STR);
            $sth->execute();

            return $this->get($key);
        }

        public function get($key)
        {
            $sth = $this->db->prepare("SELECT value FROM constants WHERE name = :name");
            $sth->bindParam(':name', $key, PDO::PARAM_STR);
            $sth->execute();

            return ($sth->fetch(PDO::FETCH_OBJ))->value ?? null;
        }
    }
