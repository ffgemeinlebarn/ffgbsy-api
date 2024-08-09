<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use PDO;
use FFGBSY\Services\DruckerService;

final class BonsDruckService extends BaseService
{
    private DruckerService $druckerService;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->druckerService = $container->get('drucker');
        parent::__construct($container, $logger);
    }

    public function createFromBon($bon)
    {
        $today = date('Y-m-d');
        $laufnummer = $this->druckerService->getNextLaufnummer($bon->drucker->id, $today);

        $sth = $this->db->prepare("INSERT INTO bons_druck (bons_id, datum, laufnummer) VALUES (:bons_id, :datum, :laufnummer)");
        $sth->bindParam(':bons_id', $bon->id, PDO::PARAM_INT);
        $sth->bindParam(':datum', $today, PDO::PARAM_STR);
        $sth->bindParam(':laufnummer', $laufnummer, PDO::PARAM_INT);
        $sth->execute();
        return $this->read($this->db->lastInsertId());
    }

    public function read($id)
    {
        $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE id = :id");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        return $this->singleRead($sth);
    }

    public function readByBon($bonId)
    {
        $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE bons_id = :bons_id");
        $sth->bindParam(':bons_id', $bonId, PDO::PARAM_INT);
        return $this->multiRead($sth);
    }

    public function countTries($bonId)
    {
        $sth = $this->db->prepare("SELECT COUNT(*) as count FROM bons_druck WHERE bons_id = :bons_id");
        $sth->bindParam(':bons_id', $bonId, PDO::PARAM_INT);

        if ($sth->execute()) {
            return $sth->fetch(PDO::FETCH_OBJ)->count;
        }

        return 0;
    }

    public function countSuccesses($bonId)
    {
        $sth = $this->db->prepare("SELECT COUNT(*) as count FROM bons_druck WHERE bons_id = :bons_id AND success = true");
        $sth->bindParam(':bons_id', $bonId, PDO::PARAM_INT);

        if ($sth->execute()) {
            return $sth->fetch(PDO::FETCH_OBJ)->count;
        }

        return 0;
    }

    public function countFails($bonId)
    {
        $sth = $this->db->prepare("SELECT COUNT(*) as count FROM bons_druck WHERE bons_id = :bons_id AND success = false");
        $sth->bindParam(':bons_id', $bonId, PDO::PARAM_INT);

        if ($sth->execute()) {
            return $sth->fetch(PDO::FETCH_OBJ)->count;
        }

        return 0;
    }

    public function readFailedBonsPrinted()
    {
        $sth = $this->db->prepare("
            SELECT 
                bestellungen.timestamp_beendet,
                bestellungen.id AS bestellung_id,
                aufnehmer.vorname,
                aufnehmer.nachname,
                tische.reihe,
                tische.nummer,
                bons.id AS bons_id,
                bons.drucker_id,
--                bons_druck.id AS bons_druck_id,
                SUM(CASE WHEN bons_druck.success = 1 THEN 1 ELSE 0 END) AS succeeded,
                SUM(CASE WHEN bons_druck.success = 0 THEN 1 ELSE 0 END) AS failed
            FROM 
                bons_druck
            LEFT JOIN
                bons ON bons.id = bons_druck.bons_id
            LEFT JOIN
                bestellungen ON bestellungen.id = bons.bestellungen_id
            LEFT JOIN
                aufnehmer ON aufnehmer.id = bestellungen.aufnehmer_id
            LEFT JOIN
                tische ON tische.id = bestellungen.tische_id
            GROUP BY
                bons_druck.bons_id
            HAVING
                failed > 0 AND
                succeeded > 0
            ORDER BY
                bestellungen.timestamp_beendet DESC
        ");

        if ($sth->execute()) {
            $arr = [];
            foreach ($sth->fetchAll(PDO::FETCH_OBJ) as $item) {
                array_push($arr, $item);
            }

            return $arr;
        }
    }

    public function readFailedBonsNotPrinted()
    {
        $sth = $this->db->prepare("
            SELECT 
                bestellungen.timestamp_beendet,
                bestellungen.id AS bestellung_id,
                aufnehmer.vorname,
                aufnehmer.nachname,
                tische.reihe,
                tische.nummer,
                bons.id AS bons_id,
                bons.drucker_id,
--                bons_druck.id AS bons_druck_id,
                SUM(CASE WHEN bons_druck.success = 1 THEN 1 ELSE 0 END) AS succeeded,
                SUM(CASE WHEN bons_druck.success = 0 THEN 1 ELSE 0 END) AS failed
            FROM 
                bons_druck
            LEFT JOIN
                bons ON bons.id = bons_druck.bons_id
            LEFT JOIN
                bestellungen ON bestellungen.id = bons.bestellungen_id
            LEFT JOIN
                aufnehmer ON aufnehmer.id = bestellungen.aufnehmer_id
            LEFT JOIN
                tische ON tische.id = bestellungen.tische_id
            GROUP BY
                bons_druck.bons_id
            HAVING
                failed > 0 AND
                succeeded = 0
            ORDER BY
                bestellungen.timestamp_beendet DESC
        ");

        if ($sth->execute()) {
            $arr = [];
            foreach ($sth->fetchAll(PDO::FETCH_OBJ) as $item) {
                array_push($arr, $item);
            }

            return $arr;
        }
    }

    public function updateResult($id, $success, $message)
    {
        $sth = $this->db->prepare("UPDATE bons_druck SET success = :success, message = :message WHERE id = :id");
        $sth->bindParam(':success', $success, PDO::PARAM_INT);
        $sth->bindParam(':message', $message, PDO::PARAM_STR);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();

        return $this->read($id);
    }

    protected function singleMap($obj)
    {
        $obj->id = $this->asNumber($obj->id);
        $obj->bons_id = $this->asNumber($obj->bons_id);
        $obj->laufnummer = $this->asNumber($obj->laufnummer);
        $obj->timestamp = $this->asIsoTimestamp($obj->timestamp);
        $obj->success = $this->asBool($obj->success);
        return $obj;
    }
}
