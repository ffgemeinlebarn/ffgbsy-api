<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class LogsService extends BaseService
    {
        public function create($data)
        {
            $device_name = null;

            $sth = $this->db->prepare("INSERT INTO logs (level, message, additional, timestamp, device_name, device_ip) VALUES (:level, :message, :additional, :timestamp, :device_name, :device_ip)");
            $sth->bindParam(':level', $data['level'], PDO::PARAM_INT);
            $sth->bindParam(':message', $data['message'], PDO::PARAM_STR);
            $sth->bindParam(':additional', $data['additional'], PDO::PARAM_STR);
            $sth->bindParam(':timestamp', $data['timestamp'], PDO::PARAM_STR);
            $sth->bindParam(':device_name', $device_name, PDO::PARAM_STR);
            $sth->bindParam(':device_ip', $data['device_ip'], PDO::PARAM_STR);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read()
        {
            $sth = $this->db->prepare("SELECT * FROM logs ORDER BY timestamp DESC");
            return $this->multiRead($sth);
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->level = $this->asNumber($obj->level);
            return $obj;
        }
    }
