<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class BestellungenService extends BaseService
    {
        private $grundprodukteService = null;
        private $produkteService = null;
        private $aufnehmerService = null;
        private $tischeService = null;
        private $bestellpositionenService = null;

        public function __construct(ContainerInterface $container)
        {
            $this->grundprodukteService = $container->get('grundprodukte');
            $this->produkteService = $container->get('produkte');
            $this->aufnehmerService = $container->get('aufnehmer');
            $this->tischeService = $container->get('tische');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            parent::__construct($container);
        }

        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO bestellungen (tische_id, timestamp_begonnen, aufnehmer_id, ip) VALUES (:tische_id, :timestamp_begonnen, :aufnehmer_id, :ip)");
            $sth->bindParam(':tische_id', $data['tisch']['id'], PDO::PARAM_INT);
            $sth->bindParam(':timestamp_begonnen', $data['timestamp_begonnen'], PDO::PARAM_STR);
            $sth->bindParam(':aufnehmer_id', $data['aufnehmer']['id'], PDO::PARAM_INT);
            $sth->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $sth->execute();

            $bestellungId = $this->db->lastInsertId();

            foreach($data['bestellpositionen'] as $position)
            {
                $this->bestellpositionenService->addToBestellung($bestellungId, $position);
            }

            // Grundprodukte

            return $this->read($bestellungId);
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM bestellungen WHERE id = :id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                $sth->execute();
                $bestellung = $sth->fetch(PDO::FETCH_OBJ);

                $bestellung->bestellpositionen = $this->bestellpositionenService->readByBestellung($bestellung->id);                

                $bestellung->summe = 0.00;
                foreach($bestellung->bestellpositionen as $position)
                {
                    $bestellung->summe += $position->summe;
                    $position->produkt = $this->produkteService->read($position->produkte_id);
                }
                
                $bestellung->aufnehmer = $this->aufnehmerService->read($bestellung->aufnehmer_id);
                $bestellung->tisch = $this->tischeService->read($bestellung->tische_id);

                return $this->singleMap($bestellung);
            }
        }

        public function checkAvailabilityForBestellpositionen($bestellpositionen)
        {
            $notAvailableProdukte = [];
            foreach($bestellpositionen as $position)
            {
                $availability = $this->grundprodukteService->checkAvailablityByProduktId($position['produkt']['id'], $position['anzahl']);
                
                if (!$availability)
                {
                    array_push ($notAvailableProdukte, $position['produkt']['name']);
                }
            }
    
            if (($num = count($notAvailableProdukte)) > 0)
            {
                $produkt = ($num > 1) ? "Die Produkte" : "Das Produkt";
                $ist = ($num > 1) ? "sind" : "ist";
                return "Die Bestellung wurde nicht angelegt. $produkt '" . implode("', '", $notAvailableProdukte) . "' $ist aufgrund nicht vorhandener Grundprodukte aktuell leider nicht verfügbar!";
            }
    
            return false;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->tische_id = $this->asNumber($obj->tische_id);
            $obj->aufnehmer_id = $this->asNumber($obj->aufnehmer_id);
            $obj->summe = $this->asDecimal($obj->summe);
            return $obj;
        }
    }
