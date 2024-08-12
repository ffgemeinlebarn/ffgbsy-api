<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use PDO;

final class ProdukteinteilungenService extends BaseService
{
    public function create($data)
    {
        $sth = $this->db->prepare("INSERT INTO produkteinteilungen (name, sortierindex) VALUES (:name, :sortierindex)");
        $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
        $sth->execute();

        return $this->read($this->db->lastInsertId());
    }

    public function read($id = null)
    {
        if ($id != null) {
            $sth = $this->db->prepare("SELECT * FROM produkteinteilungen WHERE id=:id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->singleRead($sth);
        } else {
            $sth = $this->db->prepare("SELECT * FROM produkteinteilungen ORDER BY sortierindex ASC");
            return $this->multiRead($sth);
        }
    }

    public function readByProduktkategorie($id)
    {
        $sth = $this->db->prepare("SELECT * FROM produkteinteilungen WHERE produktkategorien_id = :produktkategorien_id");
        $sth->bindParam(':produktkategorien_id', $id, PDO::PARAM_INT);
        return $this->multiRead($sth);
    }

    public function update($data)
    {
        $sth = $this->db->prepare("UPDATE produkteinteilungen SET name=:name, sortierindex=:sortierindex WHERE id=:id");
        $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
        $sth->execute();

        return $this->read($data['id']);
    }

    public function delete($id)
    {
        $sth = $this->db->prepare("DELETE FROM produkteinteilungen WHERE id = :id");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        return $sth->execute();
    }

    protected function singleMap($obj)
    {
        $obj->id = $this->asNumber($obj->id);
        $obj->sortierindex = $this->asNumber($obj->sortierindex);
        return $obj;
    }
}
