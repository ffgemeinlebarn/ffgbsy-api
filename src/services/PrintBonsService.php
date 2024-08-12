<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use PDO;
use FFGBSY\Services\BonsService;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\BonsDruckService;
use FFGBSY\Services\PrintService;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\AufnehmerService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\AdminNotificationsService;

final class PrintBonsService extends BaseService
{
    private BonsService $bonsService;
    private BestellungenService $bestellungenService;
    private BonsDruckService $bonsDruckService;
    private DruckerService $druckerService;
    private PrintService $printService;
    private TischeService $tischeService;
    private AufnehmerService $aufnehmerService;
    private BestellpositionenService $bestellpositionenService;
    private AdminNotificationsService $adminNotificationsService;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->bonsService = $container->get('bons');
        $this->bestellungenService = $container->get('bestellungen');
        $this->bonsDruckService = $container->get('bonsDruck');
        $this->printService = $container->get('print');
        $this->druckerService = $container->get('drucker');
        $this->tischeService = $container->get('tische');
        $this->aufnehmerService = $container->get('aufnehmer');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->adminNotificationsService = $container->get('adminNotifications');
        parent::__construct($container, $logger);
    }

    public function printMultipleBonsByIds($bonIds)
    {
        $besllbonsDrucke = [];

        foreach ($bonIds as $bonId) {
            array_push($besllbonsDrucke, $this->printSingleBonById($bonId));
        }

        return $besllbonsDrucke;
    }

    public function printSingleBonById($id)
    {
        $bon = $this->bonsService->read($id);
        $bonDruck = $this->bonsDruckService->createFromBon($bon);
        $tisch = $this->tischeService->readByBon($bon->id);
        $drucker = $this->druckerService->read($bon->drucker->id);
        $bestellpositionen = $this->bestellpositionenService->readByBon($bon->id);
        $aufnehmer = $bon->bestellung->aufnehmer;
        $aufnehmerName = "{$aufnehmer->vorname} {$aufnehmer->nachname[0]}.";

        $setup = $this->printService->setupPrinter($drucker);

        if ($setup->success) {
            $printer = $setup->printer;

            if ($bon->type == 'bestellung') {
                $this->printBestellbon($printer, $tisch, $drucker, $bon->bestellung->id, $bon->id, $aufnehmerName, $bestellpositionen, $bonDruck);
            }

            if ($bon->type == 'storno') {
                $this->printStornobon($printer, $tisch, $drucker, $bon->bestellung->id, $bon->id, $aufnehmerName, $bestellpositionen, $bonDruck);
            }

            $this->printService->printFinish($printer);
        }
        else {
            $this->adminNotificationsService->sendMessage("Bon Druck fehlgeschlagen", "Bon: {$bon->id}\nBestellung:{$bon->bestellung->id}\nDrucker: {$drucker->name} ({$drucker->id})");
        }

        return $this->bonsDruckService->updateResult($bonDruck->id, $setup->success, $setup->message);
    }

    public function printBestellbonsOfBestellungById($id)
    {
        $bestellung = $this->bestellungenService->read($id);
        $bonIds = array_map(function ($bon) {
            return $bon->id;
        }, $bestellung->bestellbons);
        
        return $this->printMultipleBonsByIds($bonIds);
    }

    private function printBestellbon($printer, $tisch, $drucker, $bestellungId, $bonId, $aufnehmerName, $bestellpositionen, $bonDruck)
    {
        $this->printService->printHeader($printer);
        $this->printService->printTisch($printer, $tisch);
        $this->printService->printBestellpositionenHeader($printer);
        $this->printService->printBestellpositionen($printer, $bestellpositionen);
        $this->printService->printImprint($printer);
        $this->printService->printInfo($printer, $bestellungId, $bonId, $bonDruck->id, $aufnehmerName, $bonDruck->timestamp);
        $this->printService->printLaufnummernBlock($printer, $drucker->name, $bonDruck->laufnummer);
    }

    private function printStornobon($printer, $tisch, $drucker, $bestellungId, $bonId, $aufnehmerName, $bestellpositionen, $bonDruck)
    {
        $this->printService->printStornoMark($printer);
        $this->printService->printTisch($printer, $tisch);
        $this->printService->printBestellpositionenHeader($printer);
        $this->printService->printBestellpositionen($printer, $bestellpositionen);
        $this->printService->printInfo($printer, $bestellungId, $bonId, $bonDruck->id, $aufnehmerName, $bonDruck->timestamp);
        $this->printService->printLaufnummernBlock($printer, $drucker->name, $bonDruck->laufnummer);
    }
}
