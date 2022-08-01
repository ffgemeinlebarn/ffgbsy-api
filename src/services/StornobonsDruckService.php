<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\BestellpositionenService;
    use FFGBSY\Services\PrintService;

    final class StornobonsDruckService extends BaseService
    {
        private DruckerService $druckerService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            parent::__construct($container);
        }

        public function createFromStornobon($stornobon)
        {
            $today = date('Y-m-d');
            $laufnummer = $this->druckerService->getNextLaufnummer($stornobon['drucker']['id'], $today);

            $sth = $this->db->prepare("INSERT INTO stornobons_druck (stornobons_id, datum, laufnummer) VALUES (:stornobons_id, :datum, :laufnummer)");
            $sth->bindParam(':stornobons_id', $stornobon['id'], PDO::PARAM_INT);
            $sth->bindParam(':datum', $today, PDO::PARAM_STR);
            $sth->bindParam(':laufnummer', $laufnummer, PDO::PARAM_INT);
            $sth->execute();
            return $this->read($this->db->lastInsertId());
        }

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM stornobons_druck WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->singleRead($sth);
        }

        public function readByStornobon($stornobonsId)
        {
            $sth = $this->db->prepare("SELECT * FROM stornobons_druck WHERE stornobons_id = :stornobons_id");
            $sth->bindParam(':stornobons_id', $stornobonsId, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        public function updateResult($id, $success, $message)
        {
            $sth = $this->db->prepare("UPDATE stornobons_druck SET success = :success, message = :message WHERE id = :id");
            $sth->bindParam(':success', $success, PDO::PARAM_INT);
            $sth->bindParam(':message', $message, PDO::PARAM_STR);
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($id);
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->stornobons_id = $this->asNumber($obj->stornobons_id);
            $obj->laufnummer = $this->asNumber($obj->laufnummer);
            $obj->success = $this->asBool($obj->success);
            return $obj;
        }
    }
