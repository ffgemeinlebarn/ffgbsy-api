<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class NotificationsService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO notifications (title, message, author) VALUES (:title, :message, :author)");
            $sth->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $sth->bindParam(':message', $data['message'], PDO::PARAM_STR);
            $sth->bindParam(':author', $data['author'], PDO::PARAM_STR);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM notifications WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->singleRead($sth);
        }

        public function readUntil($until)
        {
            $sth = $this->db->prepare("SELECT * FROM notifications WHERE timestamp < :timestamp ORDER BY timestamp DESC");
            $sth->bindParam(':timestamp', $until, PDO::PARAM_STR);
            return $this->multiRead($sth);
        }

        public function readSince($since)
        {
            $sth = $this->db->prepare("SELECT * FROM notifications WHERE timestamp > :timestamp ORDER BY timestamp DESC");
            $sth->bindParam(':timestamp', $since, PDO::PARAM_STR);
            return $this->multiRead($sth);
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->timestamp = $this->asDatetime($obj->timestamp);
            return $obj;
        }
    }
