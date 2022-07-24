<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class AufnehmerService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO aufnehmer (vorname, nachname, aktiv, zoom_level) VALUES (:vorname, :nachname, :aktiv, :zoom_level)");
            $sth->bindParam(':vorname', $data['vorname'], PDO::PARAM_STR);
            $sth->bindParam(':nachname', $data['nachname'], PDO::PARAM_STR);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':zoom_level', $data['zoom_level'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM aufnehmer WHERE id = :id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM aufnehmer ORDER BY nachname ASC, vorname ASC");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE aufnehmer SET vorname = :vorname, nachname = :nachname, aktiv = :aktiv, zoom_level = :zoom_level WHERE id = :id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':vorname', $data['vorname'], PDO::PARAM_STR);
            $sth->bindParam(':nachname', $data['nachname'], PDO::PARAM_STR);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':zoom_level', $data['zoom_level'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM aufnehmer WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $sth->execute();
            
            return $result;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->aktiv = $this->asBool($obj->aktiv);
            $obj->zoom_level = $this->asNumber($obj->zoom_level);
            return $obj;
        }
    }
