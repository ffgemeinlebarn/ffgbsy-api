<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use PDO;

    final class TischeService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO tische (reihe, nummer, tischkategorien_id, aktiv, sortierindex) VALUES (:reihe, :nummer, :tischkategorien_id, :aktiv, :sortierindex)");
            $sth->bindParam(':reihe', $data['reihe'], PDO::PARAM_STR);
            $sth->bindParam(':nummer', $data['nummer'], PDO::PARAM_INT);
            $sth->bindParam(':tischkategorien_id', $data['tischkategorie']['id'], PDO::PARAM_INT);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
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
                $sth = $this->db->prepare("SELECT * FROM tische ORDER BY sortierindex ASC");
                return $this->multiRead($sth);
            }
        }

        public function readByTischkategorieId($id)
        {
            $sth = $this->db->prepare("SELECT * FROM tische WHERE tischkategorien_id=:id ORDER BY sortierindex ASC");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        public function readByBon($id)
        {
            $sth = $this->db->prepare("SELECT tische.* FROM bons LEFT JOIN bestellungen ON bestellungen.id = bons.bestellungen_id LEFT JOIN tische ON tische.id = bestellungen.tische_id WHERE bons.id = :id LIMIT 1");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            return $this->singleRead($sth);
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE tische SET reihe = :reihe, nummer = :nummer, tischkategorien_id = :tischkategorien_id, aktiv = :aktiv, sortierindex = :sortierindex WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':reihe', $data['reihe'], PDO::PARAM_STR);
            $sth->bindParam(':nummer', $data['nummer'], PDO::PARAM_INT);
            $sth->bindParam(':tischkategorien_id', $data['tischkategorie']['id'], PDO::PARAM_INT);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM tische WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->nummer = $this->asNumberOrNull($obj->nummer);
            $obj->tischkategorien_id = $this->asNumber($obj->tischkategorien_id);
            $obj->aktiv = $this->asBool($obj->aktiv);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
