<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\BestellbonsService;
    use FFGBSY\Services\StornobonsService;
    use FFGBSY\Application\Exceptions\HttpBadRequestException;
    
    final class BestellungenService extends BaseService
    {
        private $grundprodukteService = null;
        private $produkteService = null;
        private $aufnehmerService = null;
        private $tischeService = null;
        private $bestellpositionenService = null;
        private BestellbonsService $bestellbonsService;
        private StornobonsService $stornobonsService;

        public function __construct(ContainerInterface $container)
        {
            $this->grundprodukteService = $container->get('grundprodukte');
            $this->produkteService = $container->get('produkte');
            $this->aufnehmerService = $container->get('aufnehmer');
            $this->tischeService = $container->get('tische');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->bestellbonsService = $container->get('bestellbons');
            $this->stornobonsService = $container->get('stornobons');
            parent::__construct($container);
        }

        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO bestellungen (tische_id, timestamp_begonnen, aufnehmer_id, device_name, device_ip) VALUES (:tische_id, :timestamp_begonnen, :aufnehmer_id, :device_name, :device_ip)");
            $sth->bindParam(':tische_id', $data['tisch']['id'], PDO::PARAM_INT);
            $sth->bindParam(':timestamp_begonnen', $data['timestamp_begonnen'], PDO::PARAM_STR);
            $sth->bindParam(':aufnehmer_id', $data['aufnehmer']['id'], PDO::PARAM_INT);
            $sth->bindParam(':device_name', $data['device_name'], PDO::PARAM_STR);
            $sth->bindParam(':device_ip', $data['device_ip'], PDO::PARAM_STR);
            $sth->execute();

            $bestellungId = $this->db->lastInsertId();

            // Bestellpositionen hinzuf??gen
            foreach($data['bestellpositionen'] as $bestellposition)
            {
                $this->bestellpositionenService->addToBestellung($bestellungId, $bestellposition);
                $this->grundprodukteService->reduceByProduktId($bestellposition['produkt']['id'], $bestellposition['anzahl']);
            }

            return $this->read($bestellungId);
        }

        public function read($id = null, $filter = [])
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM bestellungen WHERE id = :id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                $sth->execute();
                $bestellung = $this->singleRead($sth);

                $bestellung->summe = 0;
                $bestellung->bestellpositionen = $this->bestellpositionenService->readByBestellung($bestellung->id);
                foreach($bestellung->bestellpositionen as $position)
                {
                    $bestellung->summe += $position->summe;
                }
                $bestellung->aufnehmer = $this->aufnehmerService->read($bestellung->aufnehmer_id);
                $bestellung->tisch = $this->tischeService->read($bestellung->tische_id);
                $bestellung->bestellbons = $this->bestellbonsService->readByBestellung($bestellung->id);
                $bestellung->stornobons = $this->stornobonsService->readByBestellung($bestellung->id);

                return $bestellung;
            }
            else
            {
                $sql = "SELECT * FROM bestellungen WHERE 1=1";
                $aufnehmer = isset($filter['aufnehmerId']);
                $tisch = isset($filter['tischId']);
                $limit = isset($filter['limit']);

                if ($aufnehmer)
                {
                    $sql .= " AND bestellungen.aufnehmer_id = :aufnehmer_id";
                }

                if ($tisch)
                {
                    $sql .= " AND bestellungen.tische_id = :tische_id";
                }

                $sql .= " ORDER BY timestamp_beendet DESC";

                if ($limit)
                {
                    $sql .= " LIMIT :limit";
                }

                $sth = $this->db->prepare($sql);
                
                if ($aufnehmer)
                {
                    $sth->bindParam(':aufnehmer_id', $filter['aufnehmerId'], PDO::PARAM_INT);
                }

                if ($tisch)
                {
                    $sth->bindParam(':tische_id', $filter['tischId'], PDO::PARAM_INT);
                }

                if ($limit)
                {
                    $sth->bindParam(':limit', $filter['limit'], PDO::PARAM_INT);
                }

                $sth->execute();
                $bestellungen = $this->multiRead($sth);
                
                foreach($bestellungen as $bestellung)
                {
                    $bestellung->bestellpositionen = $this->bestellpositionenService->readByBestellung($bestellung->id);
                    foreach($bestellung->bestellpositionen as $position)
                    {
                        $bestellung->summe += $position->summe;
                    }
                    $bestellung->aufnehmer = $this->aufnehmerService->read($bestellung->aufnehmer_id);
                    $bestellung->tisch = $this->tischeService->read($bestellung->tische_id);
                    $bestellung->bestellbons = $this->bestellbonsService->readByBestellung($bestellung->id);
                    $bestellung->stornobons = $this->stornobonsService->readByBestellung($bestellung->id);
                }

                return $bestellungen;
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
                
                throw new HttpBadRequestException("Die Bestellung wurde nicht angelegt. $produkt '" . implode("', '", $notAvailableProdukte) . "' $ist aufgrund nicht vorhandener Grundprodukte aktuell leider nicht verf??gbar!");
            }
    
            return false;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->tische_id = $this->asNumber($obj->tische_id);
            $obj->aufnehmer_id = $this->asNumber($obj->aufnehmer_id);
            return $obj;
        }
    }
