<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class GrundprodukteService extends BaseService
    {
        public function create($data)
        {   
            $sth = $this->db->prepare("INSERT INTO grundprodukte (name, bestand, einheit) VALUES (:name, :bestand, :einheit)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':bestand', $data['bestand'], PDO::PARAM_INT);
            $sth->bindParam(':einheit', $data['einheit'], PDO::PARAM_STR);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM grundprodukte WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM grundprodukte");
                return $this->multiRead($sth);
            }
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE grundprodukte SET name = :name, bestand=:bestand, einheit=:einheit WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':bestand', $data['bestand'], PDO::PARAM_INT);
            $sth->bindParam(':einheit', $data['einheit'], PDO::PARAM_STR);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM grundprodukte WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $sth->execute();
            
            return $result;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->bestand = $this->asNumber($obj->bestand);
            return $obj;
        }
    }
