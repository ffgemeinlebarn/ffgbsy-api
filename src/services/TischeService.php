<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class TischeService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO tische (reihe, nummer, tischkategorien_id, sortierindex) VALUES (:reihe, :nummer, :tischkategorien_id, :sortierindex)");
            $sth->bindParam(':reihe', $data['reihe'], PDO::PARAM_STR);
            $sth->bindParam(':nummer', $data['nummer'], PDO::PARAM_INT);
            $sth->bindParam(':tischkategorien_id', $data['tischkategorie']['id'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM tische WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM tische");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE tische SET reihe = :reihe, nummer = :nummer, tischkategorien_id = :tischkategorien_id, sortierindex = :sortierindex WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':reihe', $data['reihe'], PDO::PARAM_STR);
            $sth->bindParam(':nummer', $data['nummer'], PDO::PARAM_INT);
            $sth->bindParam(':tischkategorien_id', $data['tischkategorie']['id'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM tische WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $sth->execute();
            
            return $result;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->nummer = $this->asNumber($obj->nummer);
            $obj->tischkategorien_id = $this->asNumber($obj->tischkategorien_id);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
