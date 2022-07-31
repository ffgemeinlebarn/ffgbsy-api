<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
    use Mike42\Escpos\PrintConnectors\FilePrintConnector;
    use Mike42\Escpos\Printer;
    use Mike42\Escpos\EscposImage;

    const LINE_AND_BREAK = "------------------------------------------------\n";
    const SHORT_LINE_AND_BREAK = "------------\n";
    const PRINTER_TIMEOUT = 3;

    final class PrintService extends BaseService
    {
        private $druckerService = null;
        private $bestellpositionenService = null;
        private $bestellungenService = null;
        private $constantsService = null;
        private $bonsService = null;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->bestellungenService = $container->get('bestellungen');
            $this->constantsService = $container->get('constants');
            $this->bonsService = $container->get('bons');
            parent::__construct($container);
        }
        
        public function createBonsAndPrintBestellung($bestellungId)
        {
            $affectedDruckerIds = $this->bestellungenService->getAffectedDruckerIds($bestellungId);

            foreach($affectedDruckerIds as $druckerId)
            {
                $today = date('Y-m-d');

                // Get last laufnummer für heute
                $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE drucker_id = :drucker_id AND datum = :datum ORDER BY laufnummer DESC");
                $sth->bindParam(':datum', $today, PDO::PARAM_STR);
                $sth->bindParam(':drucker_id', $druckerId, PDO::PARAM_INT);
                $sth->execute();
                $laufnummer_row = $sth->fetch();
                $laufnummer = $laufnummer_row ? ($laufnummer_row['laufnummer'] + 1) : 1;
    
                // Bon in Datenbank anlegen: storno_id, timestamp_gedruckt, result, result_message --> default values
                $sth = $this->db->prepare("INSERT INTO bons_druck (bestellungen_id, drucker_id, datum, laufnummer) VALUES (:bestellungen_id, :drucker_id, :datum, :laufnummer)");
                $sth->bindParam(':bestellungen_id', $bestellungId, PDO::PARAM_INT);
                $sth->bindParam(':drucker_id', $druckerId, PDO::PARAM_INT);
                $sth->bindParam(':datum', $today, PDO::PARAM_STR);
                $sth->bindParam(':laufnummer', $laufnummer, PDO::PARAM_INT);
                $sth->execute();

                $bonId = $this->db->lastInsertId();

                $this->printBonById($bonId);
            }

            return $this->bonsService->readByBestellung($bestellungId);
        }

        public function printBonById($bonId)
        {
            $sth = $this->db->prepare("SELECT * FROM bons_druck WHERE id = :id");
            $sth->bindParam(':id', $bonId, PDO::PARAM_INT);
            $sth->execute();
            $bon = $sth->fetch(PDO::FETCH_OBJ);

            $bestellungId = $bon->bestellungen_id;
            $druckerId = $bon->drucker_id;
            $bon->drucker = $this->druckerService->read($druckerId);
            $drucker = $bon->drucker;
            $bestellung = $this->bestellungenService->read($bestellungId);
            $bestellpositionen = $this->bestellpositionenService->readByBestellungAndDrucker($bestellungId, $druckerId);

            $printer = null;

            try
            {    
                $connector = new NetworkPrintConnector($drucker->ip, $drucker->port, PRINTER_TIMEOUT);
                $printer = new Printer($connector);

                $printer->selectPrintMode();
                $printer->setFont(Printer::FONT_A);
                $printer->setDoubleStrike(true);
                $printer->setLineSpacing(65);

                mb_internal_encoding("utf-8");

                $this->printHeader($printer);
                $this->printTisch($printer, $bestellung->tisch);
                $this->printBestellpositionen($printer, $bestellpositionen);
                $this->printFooterImprint($printer);
                $this->printFooterQR($printer, $bestellung);
                $this->printFooterLaufnummer($printer, $bon);

                $printer->setTextSize(1,1);
                $printer->text(" \n");
                $printer->cut();

                // Set Result of Bon Printing
                $result = true;
                $message = null;

                $sth = $this->db->prepare("UPDATE bons_druck SET result = :result, result_message = :result_message WHERE id = :id");
                $sth->bindParam(':id', $bonId, PDO::PARAM_INT);
                $sth->bindParam(':result', $result, PDO::PARAM_INT);
                $sth->bindParam(':result_message', $message, PDO::PARAM_STR);
                $sth->execute();
            }
            catch (\Exception $e)
            {
                $result = false;
                $message = $e->getMessage();

                $sth = $this->db->prepare("UPDATE bons_druck SET result = :result, result_message = :result_message WHERE id = :id");
                $sth->bindParam(':id', $bonId, PDO::PARAM_INT);
                $sth->bindParam(':result', $result, PDO::PARAM_INT);
                $sth->bindParam(':result_message', $message, PDO::PARAM_STR);
                $sth->execute();
            }
            finally
            {
                if ($printer)
                {
                    $printer->close();
                }
            }
        }

        private function printHeader($printer)
        {
            $headerImage = $this->constantsService->get('event_image');
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            if ($headerImage != null)
            {
                $img = EscposImage::load($headerImage);
                $printer->graphics($img);
            }
            else
            {
                $printer->setTextSize(2,2);
                $printer->text("{$this->constantsService->get('event_name')}\n");
                $printer->setTextSize(1,1);
                $printer->text("{$this->constantsService->get('event_date')}\n");
            }

            $printer->feed(2);
        }

        private function printTisch($printer, $tisch)
        {
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1,1);
            $printer->text(LINE_AND_BREAK);

            $printer->setTextSize(2,2);
            $printer->text("Tisch: {$tisch->reihe} {$tisch->nummer}\n");

            $printer->setTextSize(1,1);
            $printer->text(LINE_AND_BREAK);
            $printer->feed(1);
        }

        private function printBestellpositionen($printer, $bestellpositionen)
        {
            $summe = $this->bestellpositionenService->calculateSummeByBestellpositionen($bestellpositionen);
            //  2+1/30 /1+5    /1/1+6     /1
            // 00/_/ABC/_/00.00/€/_/000,00/€

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1,1);
            $printer->setEmphasis(true);
            $printer->text("Artikel                           Einzel  Gesamt\n");
            $printer->text(LINE_AND_BREAK);
            $printer->setEmphasis(false);

            foreach($bestellpositionen as $position)
            {
                $printer->setDoubleStrike(true);
                $printer->setTextSize(1,1);
                $printer->text(str_pad($position->anzahl."x", 3));
                $printer->text(str_pad($position->produkt->name, 30));
                $printer->text(str_pad(formatEuro($position->produkt->preis), 9, " ", STR_PAD_LEFT));
                $printer->text(str_pad(formatEuro($position->summe_ohne_eigenschaften), 10, " ", STR_PAD_LEFT));
                $printer->text("\n");

                if (count($position->eigenschaften->mit))
                {
                    $printer->setTextSize(1,1);
                    $printer->setDoubleStrike(false);
                    $printer->text("Mit  » " . implode(', ', array_map(function($x) { return $x->preis > 0 ? "{$x->name} (+ " . formatEuro($x->preis) . ")" : $x->name; }, $position->eigenschaften->mit)) . "\n");
                    $printer->setDoubleStrike(true);
                }

                if (count($position->eigenschaften->ohne))
                {
                    $printer->setTextSize(1,1);
                    $printer->setDoubleStrike(false);
                    $printer->text("Ohne » " . implode(', ', array_map(function($x) { return $x->preis > 0 ? "{$x->name} (- " . formatEuro($x->preis) . ")" : $x->name; }, $position->eigenschaften->ohne)) . "\n");
                    $printer->setDoubleStrike(true);
                }

                if ($position->notiz)
                {
                    $printer->setTextSize(1,1);
                    $printer->setDoubleStrike(false);
                    $printer->text("Info » {$position->notiz}\n");
                    $printer->setDoubleStrike(true);
                }
            }

            // Summe
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setDoubleStrike(true);
            $printer->setTextSize(1,1);
            $printer->text(LINE_AND_BREAK);

            $printer->setTextSize(2,2);
            $printer->text("SUMME");
            $printer->setEmphasis(true);
            $printer->text(str_pad(formatEuro($summe), 21, " ", STR_PAD_LEFT));
            $printer->setEmphasis(false);
            $printer->text("\n");

            $printer->setTextSize(1,1);
            $printer->text(LINE_AND_BREAK);
            $printer->feed(1);
        }

        private function printFooterImprint($printer)
        {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("{$this->constantsService->get('organisation_name')}\n");
            $printer->text("{$this->constantsService->get('organisation_address')}\n");
            $printer->text("{$this->constantsService->get('organisation_email')}\n");
            $printer->feed(1);
        }

        private function printFooterQr($printer, $bestellung)
        {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->qrCode(json_encode($bestellung->id), Printer::QR_ECLEVEL_L, 5, Printer::QR_MODEL_2);
            $printer->feed(1);
        }

        private function printFooterLaufnummer($printer, $bon)
        {
            $gedruckt = date_format(date_create($bon->timestamp_gedruckt), "d.m.Y H:i:s");
            $printer->setLineSpacing(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setDoubleStrike(true);
            $printer->text("$gedruckt\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
            $printer->setTextSize(2,1);
            $printer->text("{$bon->drucker->name}\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
            $printer->setTextSize(3,3);
            $printer->text("{$bon->laufnummer}\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
        }
    }

    function formatEuro($value, $symbol_bool = true, $symbol_after = true, $symbol_blank = false)
    {
        $symbol = $symbol_bool ? "€" : "";
        $blank = $symbol_blank ? " " : "";
        $number = number_format($value, 2, ",", "");

        return $symbol_after ? "$number$blank$symbol" : "$symbol$blank$number";
    }