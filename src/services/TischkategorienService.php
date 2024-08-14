<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use PDO;

    final class TischkategorienService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO tischkategorien (name, aktiv, sortierindex) VALUES (:name, :aktiv, :sortierindex)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM tischkategorien WHERE id = :id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM tischkategorien ORDER BY sortierindex ASC");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE tischkategorien SET name=:name, aktiv=:aktiv, sortierindex=:sortierindex WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM tischkategorien WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->aktiv = $this->asBool($obj->aktiv);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
