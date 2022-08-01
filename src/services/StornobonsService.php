<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;

    final class BonsService extends BaseService
    {
        private $druckerService = null;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            parent::__construct($container);
        }

        public function read($bonId)
        {
            $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE id = :id");
            $sth->bindParam(':id', $bonId, PDO::PARAM_INT);
            return $this->singleRead($sth);
        }

        public function readByBestellung($bestellungId)
        {
            $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE bestellungen_id = :bestellungen_id");
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);
            return $this->multiRead($sth);
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->drucker_id = $this->asNumber($obj->drucker_id);
            $obj->drucker = $this->druckerService->read($obj->drucker_id);
            $obj->bestellungen_id = $this->asNumber($obj->bestellungen_id);
            $obj->storno_id = $this->asNumberOrNull($obj->storno_id);
            $obj->laufnummer = $this->asNumber($obj->laufnummer);
            $obj->result = $this->asBool($obj->result);
            return $obj;
        }
    }
