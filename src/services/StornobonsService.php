<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\TischeService;
    use FFGBSY\Services\BestellpositionenService;
    use FFGBSY\Services\StornobonsDruckService;
    use FFGBSY\Services\PrintService;

    final class StornobonsService extends BaseService
    {
        private DruckerService $druckerService;
        private TischeService $tischeService;
        private BestellpositionenService $bestellpositionenService;
        private StornobonsDruckService $stornobonsDruckService;
        private PrintService $printService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->tischeService = $container->get('tische');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->stornobonsDruckService = $container->get('stornobonsDruck');
            $this->printService = $container->get('print');
            parent::__construct($container);
        }

        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO stornobons (bestellungen_id, drucker_id) VALUES (:bestellungen_id, :drucker_id)");
            $sth->bindParam(':bestellungen_id', $data['bestellungen_id'], PDO::PARAM_STR);
            $sth->bindParam(':drucker_id', $data['drucker_id'], PDO::PARAM_STR);
            $sth->execute();

            $stornobonId = $this->db->lastInsertId();
            $sth = $this->db->prepare("INSERT INTO stornobons_bestellpositionen (anzahl, stornobons_id, bestellpositionen_id) VALUES (:anzahl, :stornobons_id, :bestellpositionen_id)");

            foreach($data['bestellpositionen'] as $bestellposition)
            {
                $bestellposition = (array) $bestellposition;
                $sth->execute(
                    array(
                        'anzahl' => $bestellposition['anzahl'], 
                        'stornobons_id' => $stornobonId, 
                        'bestellpositionen_id' => $bestellposition['id']
                    )
                );
            }

            return $this->read($stornobonId);
        }

        public function read($id)
        {
            $sth = $this->db->prepare("SELECT * FROM stornobons WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $this->addNested($this->singleRead($sth));
        }

        public function readByBestellung($bestellungId)
        {
            $sth = $this->db->prepare("SELECT * FROM stornobons WHERE bestellungen_id = :bestellungen_id");
            $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);

            $items = $this->multiRead($sth);
            foreach($items as $item)
            {
                $item = $this->addNested($item);
                $item->drucke = $this->stornobonsDruckService->readByStornobon($item->id);
            }
            return $items;
        }
        
        public function printSingle($stornobon)
        {
            $stornobonDruck = $this->stornobonsDruckService->createFromStornobon($stornobon);
            $tisch = $this->tischeService->readByStornobon($stornobon['id']);
            $drucker = $this->druckerService->read($stornobon['drucker_id']);
            $setup = $this->printService->setupPrinter($drucker);
            $bestellpositionen = $this->bestellpositionenService->readByStornobon($stornobon['id']);

            $qrData = json_encode([
                "bestellungen_id" => $stornobon['bestellungen_id'],
                "stornobons_id" => $stornobon['id']
            ]);

            if ($setup->success)
            {
                $printer = $setup->printer;

                $this->printService->printHeader($printer);
                $this->printService->printTisch($printer, $tisch);
                $this->printService->printStornoMark($printer);
                $this->printService->printBestellpositionenHeader($printer);
                $this->printService->printBestellpositionen($printer, $bestellpositionen);
                $this->printService->printStornoMark($printer);
                $this->printService->printImprint($printer);
                $this->printService->printQR($printer, $qrData);
                $this->printService->printLaufnummernBlock($printer, $stornobonDruck->timestamp, $drucker->name, $stornobonDruck->laufnummer);
                $this->printService->printFinish($printer);
            }

            return $this->stornobonsDruckService->updateResult($stornobonDruck->id, $setup->success, $setup->message);

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
