<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class ProduktbereicheService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO produktbereiche (name, farbe, drucker_id_level_0) VALUES (:name, :farbe, :drucker_id_level_0)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':farbe', $data['farbe'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id_level_0', $data['drucker_id_level_0'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM produktbereiche WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM produktbereiche");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE produktbereiche SET name=:name, farbe=:farbe, drucker_id_level_0=:drucker_id_level_0 WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':farbe', $data['farbe'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id_level_0', $data['drucker_id_level_0'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM produktbereiche WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $sth->execute();
            
            return $result;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->drucker_id_level_0 = $this->asNumberOrNull($obj->drucker_id_level_0);
            return $obj;
        }
    }
