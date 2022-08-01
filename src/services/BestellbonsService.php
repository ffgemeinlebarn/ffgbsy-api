<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\TischeService;
    use FFGBSY\Services\BestellpositionenService;
    use FFGBSY\Services\BestellbonsDruckService;
    use FFGBSY\Services\PrintService;

    final class BestellbonsService extends BaseService
    {
        private DruckerService $druckerService;
        private TischeService $tischeService;
        private BestellpositionenService $bestellpositionenService;
        private BestellbonsDruckService $bestellbonsDruckService;
        private PrintService $printService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->tischeService = $container->get('tische');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->bestellbonsDruckService = $container->get('bestellbonsDruck');
            $this->printService = $container->get('print');
            parent::__construct($container);
        }

        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO bestellbons (bestellungen_id, drucker_id) VALUES (:bestellungen_id, :drucker_id)");
            $sth->bindParam(':bestellungen_id', $data['bestellungen_id'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id', $data['drucker_id'], PDO::PARAM_STR);
            $sth->execute();

            $bestellbonId = $this->db->lastInsertId();
            $sth = $this->db->prepare("INSERT INTO bestellbons_bestellpositionen (anzahl, bestellbons_id, bestellpositionen_id) VALUES (:anzahl, :bestellbons_id, :bestellpositionen_id)");
            foreach($data['bestellpositionen'] as $bestellposition)
            {
                $bestellposition = (array) $bestellposition;
                $sth->execute(
                    array(
                        'anzahl' => $bestellposition['anzahl'], 
                        'bestellbons_id' => $bestellbonId, 
                        'bestellpositionen_id' => $bestellposition['id']
                    )
                );
            }

            return $this->read($bestellbonId);
        }

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM bestellbons WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->addNested($this->singleRead($sth));
        }

        public function readByBestellung($bestellungId)
        {
            $sth = $this->db->prepare("SELECT * FROM bestellbons WHERE bestellungen_id = :bestellungen_id");
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);

            $items = $this->multiRead($sth);
            foreach($items as $item)
            {
                $item = $this->addNested($item);
            }
            return $items;
        }

        public function printMultiple($bestellbons)
        {
            $besllbonsDrucke = [];

            foreach($bestellbons as $bestellbon)
            {
                array_push($besllbonsDrucke, $this->printBestellbon($bestellbon));
            }

            return $besllbonsDrucke;
        }
        
        public function printBestellbon($bestellbon)
        {
            $bestellbonDruck = $this->bestellbonsDruckService->createFromBestellbon($bestellbon);
            $tisch = $this->tischeService->readByBestellbon($bestellbon['id']);
            $drucker = $this->druckerService->read($bestellbon['drucker_id']);
            $setup = $this->printService->setupPrinter($drucker);
            $bestellpositionen = $this->bestellpositionenService->readByBestellbon($bestellbon['id']);

            $qrData = json_encode($bestellbon->bestellungen_id);

            if ($setup->success)
            {
                $printer = $setup->printer;

                $this->printService->printHeader($printer);
                $this->printService->printTisch($printer, $tisch);
                $this->printService->printBestellpositionenHeader($printer);
                $this->printService->printBestellpositionen($printer, $bestellpositionen);
                $this->printService->printImprint($printer);
                $this->printService->printQR($printer, $qrData);
                $this->printService->printLaufnummernBlock($printer, $bestellbonDruck->timestamp, $drucker->name, $bestellbonDruck->laufnummer);
                $this->printService->printFinish($printer);
            }

            return $this->bestellbonsDruckService->updateResult($bestellbonDruck->id, $setup->success, $setup->message);

        }

        public function getAffectedDruckerIdsForBestellung($bestellungId)
        {
            $ids = [];
            foreach($this->bestellpositionenService->readByBestellung($bestellungId) as $position)
            {
                if (!in_array($position->drucker_id, $ids))
                {
                    $ids[] = $position->drucker_id;
                }
            }

            return $ids;
        }

        private function addNested($obj)
        {
            $obj->drucker = $this->druckerService->read($obj->drucker_id);
            $obj->drucker = $this->druckerService->read($obj->drucker_id);
            return $obj;
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->bestellungen_id = $this->asNumber($obj->bestellungen_id);
            $obj->drucker_id = $this->asNumber($obj->drucker_id);
            return $obj;
        }
    }
