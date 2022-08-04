<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
    use Mike42\Escpos\Printer;
    use Mike42\Escpos\EscposImage;
    
    const QUATER_LINE_BLANK = "            \n";
    const THIRD_LINE_BLANK = "                \n";
    const HALF_LINE_BLANK = "                        \n";
    const LINE_AND_BREAK = "------------------------------------------------\n";
    const SHORT_LINE_AND_BREAK = "------------\n";
    const PRINTER_TIMEOUT = 3;

    final class PrintService extends BaseService
    {
        private $druckerService = null;
        private $bestellpositionenService = null;
        private $constantsService = null;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->bestellpositionenService = $container->get('bestellpositionen');
            $this->constantsService = $container->get('constants');
            parent::__construct($container);
        }

        /**********************************************************
        *** Printer
        **********************************************************/

        public function setupPrinter($drucker)
        {
            $result = new \stdClass();
            $result->success = false;
            $result->message = null;
            $result->printer = null;

            try
            {
                $connector = new NetworkPrintConnector($drucker->ip, $drucker->port, PRINTER_TIMEOUT);
                $result->printer = new Printer($connector);

                $result->printer->selectPrintMode();
                $result->printer->setFont(Printer::FONT_A);
                $result->printer->setDoubleStrike(true);
                $result->printer->setLineSpacing(65);

                mb_internal_encoding("utf-8");

                $result->success = true;
            }
            catch (\Exception $e)
            {
                $result->success = false;
                $result->message = $e->getMessage();
                
                if ($result->printer)
                {
                    $result->printer->close();
                }
            }
            finally
            {
                return $result;
            }
        }

        /**********************************************************
        *** Print Blocks - Bons
        **********************************************************/

        public function printHeader($printer)
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

        public function printTisch($printer, $tisch)
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

        public function printStornoMark($printer)
        {
            $printer->setReverseColors(true);
            $printer->setTextSize(4,4);
            $printer->text(QUATER_LINE_BLANK);
            $printer->text("   STORONO  \n");
            $printer->text(QUATER_LINE_BLANK);
            $printer->setTextSize(1,1);
            $printer->setReverseColors(false);
            $printer->feed(1);
        }

        public function printBestellpositionenHeader($printer)
        {
            //  2+1/30 /1+5    /1/1+6     /1
            // 00/_/ABC/_/00.00/€/_/000,00/€

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1,1);
            $printer->setEmphasis(true);
            $printer->text("Artikel                           Einzel  Gesamt\n");
            $printer->text(LINE_AND_BREAK);
            $printer->setEmphasis(false);
        }

        public function printBestellpositionen($printer, $bestellpositionen)
        {
            foreach($bestellpositionen as $position)
            {
                $printer->setDoubleStrike(true);
                $printer->setTextSize(1,1);
                $printer->text(str_pad($position->anzahl."x", 3));
                $printer->text(str_pad($position->produkt->name, 30));
                $printer->text(str_pad($this->formatEuro($position->produkt->preis), 9, " ", STR_PAD_LEFT));
                $printer->text(str_pad($this->formatEuro($position->summe_ohne_eigenschaften), 10, " ", STR_PAD_LEFT));
                $printer->text("\n");

                if (count($position->eigenschaften->mit))
                {
                    $printer->setTextSize(1,1);
                    $printer->setDoubleStrike(false);
                    $printer->text("Mit  » " . implode(', ', array_map(function($x) { return $x->preis > 0 ? "{$x->name} (+ " . $this->formatEuro($x->preis) . ")" : $x->name; }, $position->eigenschaften->mit)) . "\n");
                    $printer->setDoubleStrike(true);
                }

                if (count($position->eigenschaften->ohne))
                {
                    $printer->setTextSize(1,1);
                    $printer->setDoubleStrike(false);
                    $printer->text("Ohne » " . implode(', ', array_map(function($x) { return $x->preis > 0 ? "{$x->name} (- " . $this->formatEuro($x->preis) . ")" : $x->name; }, $position->eigenschaften->ohne)) . "\n");
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
            $printer->text(str_pad($this->formatEuro($this->bestellpositionenService->calculateSummeByBestellpositionen($bestellpositionen)), 21, " ", STR_PAD_LEFT));
            $printer->setEmphasis(false);
            $printer->text("\n");

            $printer->setTextSize(1,1);
            $printer->text(LINE_AND_BREAK);
            $printer->feed(1);
        }

        public function printImprint($printer)
        {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("{$this->constantsService->get('organisation_name')}\n");
            $printer->text("{$this->constantsService->get('organisation_address')}\n");
            $printer->text("{$this->constantsService->get('organisation_email')}\n");
            $printer->feed(1);
        }

        public function printQr($printer, string $data)
        {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->qrCode($data, Printer::QR_ECLEVEL_L, 5, Printer::QR_MODEL_2);
            $printer->feed(1);
        }

        public function printLaufnummernBlock($printer, $timestamp, $druckerName, $laufnummer)
        {
            $gedruckt = date_format(date_create($timestamp), "d.m.Y H:i:s");

            $printer->setLineSpacing(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setDoubleStrike(true);
            $printer->text("$gedruckt\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
            $printer->setTextSize(2,1);
            $printer->text("$druckerName\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
            $printer->setTextSize(3,3);
            $printer->text("$laufnummer\n");
            $printer->setTextSize(2,2);
            $printer->text(SHORT_LINE_AND_BREAK);
        }

        /**********************************************************
        *** Print Blocks - Celebration
        **********************************************************/

        public function printCelebrationHeader($printer)
        {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(3, 3);
            $printer->feed(1);
            $printer->text("Gute Arbeit!\n");
            $printer->feed(1);

            $headerImage = $this->constantsService->get('celebration_header_image');
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->graphics(EscposImage::load($headerImage));
            $printer->feed(1);
        }

        public function printCelebrationContent($printer, $num, $textArtikel, $textEinheitAndName, $bottomImage)
        {
            $datetime = date('d.m.Y H:i:s');

            $printer->setTextSize(2, 2);
            $printer->text("Das war gerade $textArtikel\n");
            $printer->setTextSize(5, 5);
            $printer->feed(1);
            $printer->text("{$num}.\n");
            $printer->feed(1);
            $printer->setTextSize(2, 2);
            $printer->text("$textEinheitAndName\n");
            $printer->feed(1);
            $printer->text("beim heurigen Fest!\n");
            $printer->setTextSize(1,1);
            $printer->feed(2);
            $printer->graphics(EscposImage::load($bottomImage));
            $printer->feed(2);
            $printer->text("$datetime");
            $printer->feed(2);
        }

        /**********************************************************
        *** Print Blocks - Finish
        **********************************************************/

        public function printFinish($printer)
        {
            $printer->setTextSize(1,1);
            $printer->text(" \n");
            $printer->cut();
                
            $printer->close();
        }

        private function formatEuro($value, $symbol_bool = true, $symbol_after = true, $symbol_blank = false)
        {
            $symbol = $symbol_bool ? "€" : "";
            $blank = $symbol_blank ? " " : "";
            $number = number_format($value, 2, ",", "");
    
            return $symbol_after ? "$number$blank$symbol" : "$symbol$blank$number";
        }
    }
