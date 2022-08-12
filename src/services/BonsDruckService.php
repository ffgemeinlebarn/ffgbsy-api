<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\PrintService;

    final class BonsDruckService extends BaseService
    {
        private DruckerService $druckerService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            parent::__construct($container);
        }

        public function createFromBon($bon)
        {
            $today = date('Y-m-d');
            $laufnummer = $this->druckerService->getNextLaufnummer($bon['drucker']['id'], $today);

            $sth = $this->db->prepare("INSERT INTO bons_druck (bons_id, datum, laufnummer) VALUES (:bons_id, :datum, :laufnummer)");
            $sth->bindParam(':bons_id', $bon['id'], PDO::PARAM_INT);
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
