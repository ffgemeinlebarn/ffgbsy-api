<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use PDO;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\AufnehmerService;
use FFGBSY\Services\BonsDruckService;
use FFGBSY\Services\BestellpositionenService;

final class BonsService extends BaseService
{
    private DruckerService $druckerService;
    private TischeService $tischeService;
    private AufnehmerService $aufnehmerService;
    private BonsDruckService $bonsDruckService;
    private BestellpositionenService $bestellpositionenService;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->druckerService = $container->get('drucker');
        $this->tischeService = $container->get('tische');
        $this->aufnehmerService = $container->get('aufnehmer');
        $this->bonsDruckService = $container->get('bonsDruck');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        parent::__construct($container, $logger);
    }

    public function create($data)
    {
        $sth = $this->db->prepare("INSERT INTO bons (type, bestellungen_id, drucker_id) VALUES (:type, :bestellungen_id, :drucker_id)");
        $sth->bindParam(':type', $data['type'], PDO::PARAM_STR);
        $sth->bindParam(':bestellungen_id', $data['bestellungen_id'], PDO::PARAM_STR);
        $sth->bindParam(':drucker_id', $data['drucker_id'], PDO::PARAM_STR);
        $sth->execute();

        $bonId = $this->db->lastInsertId();
        $sth = $this->db->prepare("INSERT INTO bons_bestellpositionen (bons_id, bestellpositionen_id) VALUES (:bons_id, :bestellpositionen_id)");
        foreach ($data['bestellpositionen'] as $bestellposition) {
            $bestellposition = (array) $bestellposition;
            $sth->execute(
                array(
                    'bons_id' => $bonId,
                    'bestellpositionen_id' => $bestellposition['id']
                )
            );
        }

        return $this->read($bonId);
    }

    public function read($id = null, $filter = [])
    {
        if ($id != null) {
            $sth = $this->db->prepare(
                "SELECT
                    bons.*,
                    bestellungen.tische_id,
                    bestellungen.aufnehmer_id,
                    bestellungen.timestamp_begonnen,
                    bestellungen.timestamp_beendet
                FROM
                    bons
                LEFT JOIN 
                    bestellungen ON bestellungen.id = bons.bestellungen_id
                WHERE
                    bons.id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);

            return $this->addNested($this->singleRead($sth));
        } else {
            $sql =
                "SELECT
                    bons.*,
                    bestellungen.tische_id,
                    bestellungen.aufnehmer_id,
                    bestellungen.timestamp_begonnen,
                    bestellungen.timestamp_beendet,
                    (SELECT COUNT(bons_druck.id) FROM bons_druck WHERE bons.id = bons_druck.bons_id) as tries,
                    (SELECT COUNT(bons_druck.id) FROM bons_druck WHERE bons.id = bons_druck.bons_id AND bons_druck.success = true) as successes,
                    (SELECT COUNT(bons_druck.id) FROM bons_druck WHERE bons.id = bons_druck.bons_id AND bons_druck.success = false) as fails
                FROM
                    bons
                LEFT JOIN 
                    bestellungen ON bestellungen.id = bons.bestellungen_id
                WHERE
                    1=1";

            $missingSuccessfulDruck = isset($filter['missingSuccessfulDruck']) ? $this->asBool($filter['missingSuccessfulDruck']) : false;
            $multipleDrucke = isset($filter['multipleDrucke']) ? $this->asBool($filter['multipleDrucke']) : false;
            $drucker = isset($filter['druckerId']);
            $tisch = isset($filter['tischId']);
            $limit = isset($filter['limit']);
            $type = isset($filter['type']);

            if ($drucker) {
                $sql .= " AND bons.drucker_id = :drucker_id";
            }

            if ($tisch) {
                $sql .= " AND bestellungen.tische_id = :tische_id";
            }

            if ($type) {
                $sql .= " AND bons.type = :type";
            }

            if ($missingSuccessfulDruck) {
                $sql .= " AND (SELECT COUNT(bons_druck.id) FROM bons_druck WHERE bons.id = bons_druck.bons_id AND bons_druck.success = true) = 0";
            }

            if ($multipleDrucke) {
                $sql .= " AND (SELECT COUNT(bons_druck.id) FROM bons_druck WHERE bons.id = bons_druck.bons_id) > 0";
            }
            
            $sql .= " ORDER BY bestellungen.timestamp_beendet DESC";

            if ($limit) {
                $sql .= " LIMIT :limit";
            }

            $sth = $this->db->prepare($sql);

            if ($drucker) {
                $sth->bindParam(':drucker_id', $filter['druckerId'], PDO::PARAM_INT);
            }

            if ($tisch) {
                $sth->bindParam(':tische_id', $filter['tischId'], PDO::PARAM_INT);
            }

            if ($limit) {
                $sth->bindParam(':limit', $filter['limit'], PDO::PARAM_INT);
            }

            if ($type) {
                $sth->bindParam(':type', $filter['type'], PDO::PARAM_STR);
            }

            $sth->execute();
            $bons = $this->multiRead($sth);

            foreach ($bons as $bon) {
                $bon = $this->addNested($bon);
            }

            return $bons;
        }
    }

    public function readByTypeAndBestellung($type, $bestellungId)
    {
        $sth = $this->db->prepare("SELECT * FROM bons WHERE type = :type AND bestellungen_id = :bestellungen_id");
        $sth->bindParam(':type', $type, PDO::PARAM_STR);
        $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);

        $items = $this->multiRead($sth);
        foreach ($items as $item) {
            $item = $this->addNested($item);
        }
        return $items;
    }

    private function addNested($obj)
    {
        $obj->drucker = $this->druckerService->read($obj->drucker_id);
        $obj->drucke = $this->bonsDruckService->readByBon($obj->id);
        $obj->bestellung = new \stdClass();
        $obj->bestellung->id = $obj->bestellungen_id;
        
        if (isset($obj->timestamp_begonnen)){
            $obj->bestellung->timestamp_begonnen = $obj->timestamp_begonnen;
        }
        if (isset($obj->timestamp_beendet)){
            $obj->bestellung->timestamp_beendet = $obj->timestamp_beendet;
        }
        if (isset($obj->aufnehmer_id)){
            $obj->bestellung->aufnehmer = $this->aufnehmerService->read($obj->aufnehmer_id);
        }
        if (isset($obj->tische_id)){
            $obj->bestellung->tisch = $this->tischeService->read($obj->tische_id);
        }

        // unset($obj->drucker_id);
        // unset($obj->bestellungen_id);
        unset($obj->timestamp_begonnen);
        unset($obj->timestamp_beendet);
        unset($obj->tische_id);
        unset($obj->aufnehmer_id);

        return $obj;
    }

    public function getAffectedDruckerIdsForBestellung($type, $bestellungId)
    {
        $ids = [];
        foreach ($this->bestellpositionenService->readByTypeAndBestellung($type, $bestellungId) as $position) {
            if (!in_array($position->drucker_id, $ids)) {
                $ids[] = $position->drucker_id;
            }
        }

        return $ids;
    }

    protected function singleMap($obj)
    {
        $obj->id = $this->asNumber($obj->id);
        $obj->bestellungen_id = $this->asNumber($obj->bestellungen_id);
        $obj->drucker_id = $this->asNumber($obj->drucker_id);

        return $obj;
    }
}
