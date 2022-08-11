<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class ProdukteService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare(
                "INSERT INTO produkte (
                    name, 
                    formal_name, 
                    preis, 
                    drucker_id_level_2, 
                    aktiv, 
                    sortierindex, 
                    produkteinteilungen_id, 
                    grundprodukte_id, 
                    grundprodukte_multiplikator,
                    celebration_active,
                    celebration_last,
                    celebration_prefix,
                    celebration_suffix
                ) VALUES (
                    :name, 
                    :formal_name, 
                    :preis, 
                    :drucker_id_level_2, 
                    :aktiv, 
                    :sortierindex, 
                    :produkteinteilungen_id, 
                    :grundprodukte_id, 
                    :grundprodukte_multiplikator,
                    :celebration_active,
                    :celebration_last,
                    :celebration_prefix,
                    :celebration_suffix
                )"
            );
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':formal_name', $data['formal_name'], PDO::PARAM_STR);
            $sth->bindParam(':preis', $data['preis'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id_level_2', $data['drucker_id_level_2'], PDO::PARAM_INT);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->bindParam(':produkteinteilungen_id', $data['produkteinteilung']['id'], PDO::PARAM_INT);
            $sth->bindParam(':grundprodukte_id', $data['grundprodukt']['id'], PDO::PARAM_STR);
            $sth->bindParam(':grundprodukte_multiplikator', $data['grundprodukte_multiplikator'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_active', $data['celebration_active'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_last', $data['celebration_last'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_prefix', $data['celebration_prefix'], PDO::PARAM_STR);
            $sth->bindParam(':celebration_suffix', $data['celebration_suffix'], PDO::PARAM_STR);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM produkte WHERE id = :id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM produkte");
                return $this->multiRead($sth);
            }
        }

        public function readAllActive()
        {
            $sth = $this->db->prepare("SELECT * FROM produkte WHERE aktiv = 1");
            return $this->multiRead($sth);
        }

        public function readByProdukteinteilung($id)
        {
            $sth = $this->db->prepare("SELECT * FROM produkte WHERE produkteinteilungen_id = :produkteinteilungen_id");
            $sth->bindParam(':produkteinteilungen_id', $id, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        public function update($data)
        {
            $sth = $this->db->prepare(
                "UPDATE 
                    produkte 
                SET 
                    name = :name, 
                    formal_name = :formal_name, 
                    preis = :preis, 
                    drucker_id_level_2 = :drucker_id_level_2, 
                    aktiv = :aktiv, 
                    sortierindex = :sortierindex, 
                    produkteinteilungen_id = :produkteinteilungen_id, 
                    grundprodukte_id = :grundprodukte_id, 
                    grundprodukte_multiplikator = :grundprodukte_multiplikator,
                    celebration_active = :celebration_active,
                    celebration_last = :celebration_last,
                    celebration_prefix = :celebration_prefix,
                    celebration_suffix = :celebration_suffix
                WHERE 
                    id = :id
                "
            );
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':formal_name', $data['formal_name'], PDO::PARAM_STR);
            $sth->bindParam(':preis', $data['preis'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id_level_2', $data['drucker_id_level_2'], PDO::PARAM_INT);
            $sth->bindParam(':aktiv', $data['aktiv'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->bindParam(':produkteinteilungen_id', $data['produkteinteilung']['id'], PDO::PARAM_INT);
            $sth->bindParam(':grundprodukte_id', $data['grundprodukt']['id'], PDO::PARAM_STR);
            $sth->bindParam(':grundprodukte_multiplikator', $data['grundprodukte_multiplikator'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_active', $data['celebration_active'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_last', $data['celebration_last'], PDO::PARAM_INT);
            $sth->bindParam(':celebration_prefix', $data['celebration_prefix'], PDO::PARAM_STR);
            $sth->bindParam(':celebration_suffix', $data['celebration_suffix'], PDO::PARAM_STR);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM produkte WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->preis = $this->asDecimal($obj->preis);
            $obj->drucker_id_level_2 = $this->asNumberOrNull($obj->drucker_id_level_2);
            $obj->aktiv = $this->asBool($obj->aktiv);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            $obj->produkteinteilungen_id = $this->asNumber($obj->produkteinteilungen_id);
            $obj->grundprodukte_id = $this->asNumber($obj->grundprodukte_id);
            $obj->grundprodukte_multiplikator = $this->asNumber($obj->grundprodukte_multiplikator);
            $obj->celebration_active = $this->asBool($obj->celebration_active);
            $obj->celebration_last = $this->asNumber($obj->celebration_last);
            return $obj;
        }
    }
