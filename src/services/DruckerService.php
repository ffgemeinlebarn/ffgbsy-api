<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class DruckerService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO drucker (name, ip, port, mac) VALUES (:name, :ip, :port, :mac)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $sth->bindParam(':port', $data['port'], PDO::PARAM_INT);
            $sth->bindParam(':mac', $data['mac'], PDO::PARAM_STR);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM drucker WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM drucker");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE drucker SET name = :name, ip = :ip, port = :port, mac = :mac WHERE id = :id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $sth->bindParam(':port', $data['port'], PDO::PARAM_INT);
            $sth->bindParam(':mac', $data['mac'], PDO::PARAM_STR);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM drucker WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $sth->execute();
            
            return $result;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->port = $this->asNumber($obj->port);
            return $obj;
        }
    }
