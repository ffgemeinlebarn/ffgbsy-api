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
                    (produkte.preis * bestellpositionen.anzahl) AS summe,
                    IFNULL(SUM(bestellpositionen_storno.anzahl), 0) AS anzahl_storono
                FROM 
                    bestellpositionen 
                LEFT JOIN 
                    produkte ON produkte.id = bestellpositionen.produkte_id 
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
                    (produkte.preis * bestellpositionen.anzahl) AS summe,
                    IFNULL(SUM(bestellpositionen_storno.anzahl), 0) AS anzahl_storono
                FROM 
                    bestellpositionen 
                LEFT JOIN 
                    produkte ON produkte.id = bestellpositionen.produkte_id 
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

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->anzahl = $this->asNumber($obj->anzahl);
            $obj->anzahl_storono = $this->asNumber($obj->anzahl_storono);
            $obj->produkte_id = $this->asNumber($obj->produkte_id);
            $obj->bestellungen_id = $this->asNumber($obj->bestellungen_id);
            $obj->eigenschaften = $this->eigenschaftenService->readByBestellposition($obj->id);
            $obj->summe = $this->asDecimal($obj->summe);
            return $obj;
        }
    }
