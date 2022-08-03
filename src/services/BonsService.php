<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\TischeService;
    use FFGBSY\Services\BestellpositionenService;
    use FFGBSY\Services\BonsDruckService;
    use FFGBSY\Services\PrintService;

    final class BonsService extends BaseService
    {
        private DruckerService $druckerService;
        private TischeService $tischeService;
        private BestellpositionenService $bestellpositionenService;
        private BonsDruckService $bonsDruckService;
        private PrintService $printService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->tischeService = $container->get('tische');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->bonsDruckService = $container->get('bonsDruck');
            $this->printService = $container->get('print');
            parent::__construct($container);
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
            foreach($data['bestellpositionen'] as $bestellposition)
            {
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

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM bons WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->addNested($this->singleRead($sth));
        }

        public function readByTypeAndBestellung($type, $bestellungId)
        {
            $sth = $this->db->prepare("SELECT * FROM bons WHERE type = :type AND bestellungen_id = :bestellungen_id");
            $sth->bindParam(':type', $type, PDO::PARAM_STR);
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);

            $items = $this->multiRead($sth);
            foreach($items as $item)
            {
                $item = $this->addNested($item);
                $item->drucke = $this->bonsDruckService->readByBon($item->id);
            }
            return $items;
        }

        public function printMultiple($bons)
        {
            $besllbonsDrucke = [];

            foreach($bons as $bon)
            {
                array_push($besllbonsDrucke, $this->printSingle($bon));
            }

            return $besllbonsDrucke;
        }
        
        public function printSingle($bon)
        {
            $bonDruck = $this->bonsDruckService->createFromBon($bon);
            $tisch = $this->tischeService->readByBon($bon['id']);
            $drucker = $this->druckerService->read($bon['drucker_id']);
            $setup = $this->printService->setupPrinter($drucker);
            $bestellpositionen = $this->bestellpositionenService->readByBon($bon['id']);

            $qrData = json_encode([
                "bestellungen_id" => $bon['bestellungen_id'],
                "bon_id" => $bon['id']
            ]);

            if ($setup->success)
            {
                $printer = $setup->printer;

                $this->printService->printHeader($printer);
                $this->printService->printTisch($printer, $tisch);
                $this->printService->printBestellpositionenHeader($printer);
                $this->printService->printBestellpositionen($printer, $bestellpositionen);
                $this->printService->printImprint($printer);
                $this->printService->printQR($printer, $qrData);
                $this->printService->printLaufnummernBlock($printer, $bonDruck->timestamp, $drucker->name, $bonDruck->laufnummer);
                $this->printService->printFinish($printer);
            }

            return $this->bonsDruckService->updateResult($bonDruck->id, $setup->success, $setup->message);

        }

        public function getAffectedDruckerIdsForBestellung($type, $bestellungId)
        {
            $ids = [];
            foreach($this->bestellpositionenService->readByTypeAndBestellung($type, $bestellungId) as $position)
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
