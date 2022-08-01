<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\BestellpositionenService;
    use FFGBSY\Services\PrintService;

    final class BestellbonsDruckService extends BaseService
    {
        private DruckerService $druckerService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            parent::__construct($container);
        }

        public function createFromBestellbon($bestellbon)
        {
            $today = date('Y-m-d');
            $laufnummer = $this->druckerService->getNextLaufnummer($bestellbon['drucker']['id'], $today);

            $sth = $this->db->prepare("INSERT INTO bestellbons_druck (bestellbons_id, datum, laufnummer) VALUES (:bestellbons_id, :datum, :laufnummer)");
            $sth->bindParam(':bestellbons_id', $bestellbon['id'], PDO::PARAM_INT);
            $sth->bindParam(':datum', $today, PDO::PARAM_STR);
            $sth->bindParam(':laufnummer', $laufnummer, PDO::PARAM_INT);
            $sth->execute();
            return $this->read($this->db->lastInsertId());
        }

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM bestellbons_druck WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->singleRead($sth);
        }

        public function readByBestellbon($bestellbonsId)
        {
            $sth = $this->db->prepare("SELECT * FROM bestellbons_druck WHERE bestellbons_id = :bestellbons_id");
            $sth->bindParam(':bestellbons_id', $bestellbonsId, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        public function updateResult($id, $success, $message)
        {
            $sth = $this->db->prepare("UPDATE bestellbons_druck SET success = :success, message = :message WHERE id = :id");
            $sth->bindParam(':success', $success, PDO::PARAM_INT);
            $sth->bindParam(':message', $message, PDO::PARAM_STR);
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($id);
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->bestellbons_id = $this->asNumber($obj->bestellbons_id);
            $obj->laufnummer = $this->asNumber($obj->laufnummer);
            $obj->success = $this->asBool($obj->success);
            return $obj;
        }
    }
