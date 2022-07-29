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

        public function addToBestellposition($bestellpositionId, $data)
        {
            if(boolval($data['in_produkt_enthalten']) != boolval($data['aktiv']))
            {
                $sth = $this->db->prepare("INSERT INTO bestellpositionen_eigenschaften (bestellpositionen_id, eigenschaften_id, in_produkt_enthalten, aktiv) VALUES (:bestellpositionen_id, :eigenschaften_id, :in_produkt_enthalten, :aktiv)");
                $sth->bindParam(':bestellpositionen_id', $bestellpositionId, PDO::PARAM_INT);
                $sth->bindParam(':eigenschaften_id', $data['id'], PDO::PARAM_INT);
                $sth->bindParam(':in_produkt_enthalten', $data['in_produkt_enthalten'], PDO::PARAM_INT);
                $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
                $sth->execute();
            }

            return $this->readByBestellposition($bestellpositionId);
        }

        public function readByBestellposition($bestellpositionId)
        {
            $sth = $this->db->prepare(
                "SELECT 
                    * 
                FROM 
                    eigenschaften 
                LEFT JOIN 
                    bestellpositionen_eigenschaften ON eigenschaften.id = bestellpositionen_eigenschaften.eigenschaften_id 
                WHERE 
                    bestellpositionen_eigenschaften.bestellpositionen_id = :bestellpositionen_id AND
                    bestellpositionen_eigenschaften.in_produkt_enthalten = 0 AND
                    bestellpositionen_eigenschaften.aktiv = 1
            ");
            $sth->bindParam(':bestellpositionen_id', $bestellpositionId, PDO::PARAM_INT);
            $sth->execute();

            $mit = [];
            foreach($sth->fetchAll(PDO::FETCH_OBJ) as $item)
            {
                array_push($mit, $this->singleMapOfBestellposition($item));
            }
            
            $sth = $this->db->prepare(
                "SELECT 
                    * 
                FROM 
                    eigenschaften 
                LEFT JOIN 
                    bestellpositionen_eigenschaften ON eigenschaften.id = bestellpositionen_eigenschaften.eigenschaften_id 
                WHERE 
                    bestellpositionen_eigenschaften.bestellpositionen_id = :bestellpositionen_id AND
                    bestellpositionen_eigenschaften.in_produkt_enthalten = 1 AND
                    bestellpositionen_eigenschaften.aktiv = 0
            ");
            $sth->bindParam(':bestellpositionen_id', $bestellpositionId, PDO::PARAM_INT);
            $sth->execute();

            $ohne = [];
            foreach($sth->fetchAll(PDO::FETCH_OBJ) as $item)
            {
                array_push($ohne, $this->singleMapOfBestellposition($item));
            }

            $return = new \stdClass();
            $return->mit = $mit;
            $return->ohne = $ohne;

            return $return;
        }

        protected function singleMapOfBestellposition($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->preis = $this->asDecimal($obj->preis);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            $obj->in_produkt_enthalten = $this->asBool($obj->in_produkt_enthalten);
            $obj->aktiv = $this->asBool($obj->aktiv);
            return $obj;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->preis = $this->asDecimal($obj->preis);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
