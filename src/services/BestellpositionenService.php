<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class BestellpositionenService extends BaseService
    {
        private $produkteService = null;
        private $eigenschaftenService = null;

        public function __construct(ContainerInterface $container)
        {
            $this->produkteService = $container->get('produkte');
            $this->eigenschaftenService = $container->get('eigenschaften');
            parent::__construct($container);
        }

        public function addToBestellung($bestellungId, $data)
        {
            $sth = $this->db->prepare("INSERT INTO bestellpositionen (anzahl, produkte_id, notiz, bestellungen_id) VALUES (:anzahl, :produkte_id, :notiz, :bestellungen_id)");
            $sth->bindParam(':anzahl', $data['anzahl'], PDO::PARAM_INT);
            $sth->bindParam(':produkte_id', $data['produkt']['id'], PDO::PARAM_INT);
            $sth->bindParam(':notiz', $data['notiz'], PDO::PARAM_STR);
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);
            $sth->execute();

            $bestellpositionId = $this->db->lastInsertId();
            foreach($data['eigenschaften'] as $eigenschaft)
            {
                $this->eigenschaftenService->addToBestellposition($bestellpositionId, $eigenschaft);
            }

            return $this->read($bestellpositionId);
        }

        public function read($id)
        {
            $sth = $this->db->prepare(
                "SELECT 
                    bestellpositionen.*,
                    (produkte.preis * bestellpositionen.anzahl) AS summe_ohne_eigenschaften,
                    IFNULL(SUM(bestellpositionen_storno.anzahl), 0) AS anzahl_storno,
                    produktbereiche.drucker_id_level_0,
                    produktkategorien.drucker_id_level_1,
                    produkte.drucker_id_level_2
                FROM 
                    bestellpositionen 
                LEFT JOIN 
                    produkte ON produkte.id = bestellpositionen.produkte_id 
                LEFT JOIN 
                    produkteinteilungen ON produkteinteilungen.id = produkte.produkteinteilungen_id
                LEFT JOIN 
                    produktkategorien ON produktkategorien.id = produkteinteilungen.produktkategorien_id
                LEFT JOIN 
                    produktbereiche ON produktbereiche.id = produktkategorien.produktbereiche_id
                LEFT JOIN
                    bestellpositionen_storno ON bestellpositionen_storno.bestellpositionen_id = bestellpositionen.id
                WHERE
                    bestellpositionen.id = :id"
            );
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->singleRead($sth);
        }

        public function readByBestellung($bestellungId)
        {
            $sth = $this->db->prepare(
                "SELECT 
                    bestellpositionen.*,
                    (produkte.preis * bestellpositionen.anzahl) AS summe_ohne_eigenschaften,
                    IFNULL(SUM(bestellpositionen_storno.anzahl), 0) AS anzahl_storno,
                    produktbereiche.drucker_id_level_0,
                    produktkategorien.drucker_id_level_1,
                    produkte.drucker_id_level_2
                FROM 
                    bestellpositionen 
                LEFT JOIN 
                    produkte ON produkte.id = bestellpositionen.produkte_id 
                LEFT JOIN 
                    produkteinteilungen ON produkteinteilungen.id = produkte.produkteinteilungen_id
                LEFT JOIN 
                    produktkategorien ON produktkategorien.id = produkteinteilungen.produktkategorien_id
                LEFT JOIN 
                    produktbereiche ON produktbereiche.id = produktkategorien.produktbereiche_id
                LEFT JOIN
                    bestellpositionen_storno ON bestellpositionen_storno.bestellpositionen_id = bestellpositionen.id
                WHERE 
                    bestellpositionen.bestellungen_id = :bestellungen_id
                GROUP BY
                    bestellpositionen.id"
            );
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        public function storno($id)
        {
        
            $sth = $this->db->prepare("INSERT INTO bestellpositionen_storno (bestellpositionen_id, anzahl) VALUES (:bestellpositionen_id, :anzahl)");
            $sth->bindParam(':bestellpositionen_id', $bestellpositionen_id, PDO::PARAM_INT);
            $sth->bindParam(':anzahl', $anzahl, PDO::PARAM_INT);
            $data->insert->result = $sth->execute();

            $position = $this->read($id);
            

            return $this->singleRead($sth);
        }

        public function calculateSummeByBestellpositionen($bestellpositionen): float
        {
            $summe = 0;

            foreach($bestellpositionen as $position)
            {
                $summe += $position->summe_ohne_eigenschaften;

                foreach($position->eigenschaften->mit as $eigenschaft)
                {
                    $summe += $position->anzahl * $eigenschaft->preis;
                }

                foreach($position->eigenschaften->ohne as $eigenschaft)
                {
                    $summe -= $position->anzahl * $eigenschaft->preis;
                }
            }

            return $summe;
        }

        public function readByBestellungAndDrucker($bestellungId, $druckerId)
        {
            $positionen = $this->readByBestellung($bestellungId);
            $filteredPositions = [];

            foreach($positionen as $position)
            {
                if ($position->drucker_id == $druckerId)
                {
                    array_push($filteredPositions, $position);
                }
            }

            return $filteredPositions;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->anzahl = $this->asNumber($obj->anzahl);
            $obj->anzahl_storno = $this->asNumber($obj->anzahl_storno);
            $obj->produkte_id = $this->asNumber($obj->produkte_id);
            $obj->produkt = $this->produkteService->read($obj->produkte_id);
            $obj->bestellungen_id = $this->asNumber($obj->bestellungen_id);
            $obj->eigenschaften = $this->eigenschaftenService->readByBestellposition($obj->id);
            $obj->summe_ohne_eigenschaften = $this->asDecimal($obj->summe_ohne_eigenschaften);

            $obj->drucker_id_level_0 = $this->asNumberOrNull($obj->drucker_id_level_0);
            $obj->drucker_id_level_1 = $this->asNumberOrNull($obj->drucker_id_level_1);
            $obj->drucker_id_level_2 = $this->asNumberOrNull($obj->drucker_id_level_2);
            
            if ($obj->drucker_id_level_2 != null)
            {
                $obj->drucker_id = $obj->drucker_id_level_2;
            }
            else if ($obj->drucker_id_level_1 != null)
            {
                $obj->drucker_id = $obj->drucker_id_level_1;
            }
            else
            {
                $obj->drucker_id = $obj->drucker_id_level_0;
            }

            return $obj;
        }
    }
