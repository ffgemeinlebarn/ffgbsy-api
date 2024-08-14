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
            return $sth->execute();
        }

        public function checkAvailablityByProduktId($produktId, $anzahl = 1)
        {
            $sth = $this->db->prepare(
                "SELECT 
                    produkte.grundprodukte_multiplikator,
                    grundprodukte.bestand
                FROM 
                    produkte 
                LEFT JOIN 
                    grundprodukte ON grundprodukte.id = produkte.grundprodukte_id
                WHERE 
                produkte.id = :id");
            $sth->bindParam(':id', $produktId, PDO::PARAM_INT);
            $sth->execute();

            $data = $sth->fetch(PDO::FETCH_OBJ);

            return $data->bestand === NULL || ($data->grundprodukte_multiplikator * $anzahl) <= $data->bestand;
        }

        public function reduceByProduktId($produktId, $anzahl = 1)
        {
            $sth = $this->db->prepare(
                "SELECT 
                    grundprodukte.id,
                    produkte.grundprodukte_multiplikator,
                    grundprodukte.bestand
                FROM 
                    produkte 
                LEFT JOIN 
                    grundprodukte ON grundprodukte.id = produkte.grundprodukte_id
                WHERE 
                    produkte.id = :id");
            $sth->bindParam(':id', $produktId, PDO::PARAM_INT);
            $sth->execute();

            $grundprodukt = $sth->fetch(PDO::FETCH_OBJ);

            if ($grundprodukt->bestand !== null)
            {
                $neuerBestand = $grundprodukt->bestand - ($grundprodukt->grundprodukte_multiplikator * $anzahl);
    
                $sth = $this->db->prepare(
                    "UPDATE
                        grundprodukte 
                    SET 
                        bestand = :bestand
                    WHERE 
                        id = :id");
                $sth->bindParam(':bestand', $neuerBestand, PDO::PARAM_INT);
                $sth->bindParam(':id', $grundprodukt->id, PDO::PARAM_INT);
                
                return $sth->execute();
            }

            return false;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            return $obj;
        }
    }
