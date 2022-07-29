<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class EigenschaftenService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO eigenschaften (name, preis, sortierindex) VALUES (:name, :preis, :sortierindex)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':preis', $data['preis'], PDO::PARAM_STR);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM eigenschaften WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM eigenschaften ORDER BY sortierindex ASC");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE eigenschaften SET name = :name, preis = :preis, sortierindex = :sortierindex WHERE id = :id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':preis', $data['preis'], PDO::PARAM_STR);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM eigenschaften WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->preis = $this->asDecimal($obj->preis);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
