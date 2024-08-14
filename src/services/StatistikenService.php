<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use PDO;
use FFGBSY\Services\DruckerService;
use FFGBSY\Services\TischeService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\BonsDruckService;
use FFGBSY\Services\PrintService;

final class StatistikenService extends BaseService
{
    private BonsDruckService $bonsDruckService;
    private BestellpositionenService $bestellpositionenService;
    private BestellungenService $bestellungenService;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->bonsDruckService = $container->get('bonsDruck');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->bestellungenService = $container->get('bestellungen');
        parent::__construct($container, $logger);
    }

    public function timeline()
    {
        $sth = $this->db->prepare(
            "SELECT
                    SUM(bestellpositionen.anzahl) AS bestellte_produkte,
                    SUM(produkte.preis * bestellpositionen.anzahl) AS summe,
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellungen
                LEFT JOIN
                    bestellpositionen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                GROUP BY
                    datum
                ORDER BY
                    datum ASC"
        );
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        for ($i = 0; $i < count($result); $i++) {
            $quaters = [];
            for ($j = 0; $j < (24 * 4); $j++) {
                $sth = $this->db->prepare(
                    "SELECT
                            (UNIX_TIMESTAMP(bestellungen.timestamp_beendet) DIV 900) - (UNIX_TIMESTAMP(DATE(bestellungen.timestamp_beendet)) DIV 900) AS quarter,
                            REPLACE(COUNT(DISTINCT(bestellungen.id)), ',', '') AS anzahl_bestellungen,
                            REPLACE(FORMAT((60*60)/COUNT(DISTINCT(bestellungen.id)), 2), ',', '') AS bestellung_alle_x_sekunden,
                            REPLACE(FORMAT((1/((60*60)/COUNT(DISTINCT(bestellungen.id)))) * 1000, 2), ',', '') AS bestellung_frequenz_mHz,
                            REPLACE(SUM(bestellpositionen.anzahl), ',', '') AS bestellte_produkte,
                            REPLACE(SUM(produkte.preis * bestellpositionen.anzahl), ',', '') AS summe,
                            HOUR(bestellungen.timestamp_beendet) AS hour
                        FROM
                            bestellungen
                        LEFT JOIN
                            bestellpositionen ON bestellpositionen.bestellungen_id = bestellungen.id
                        LEFT JOIN
                            produkte ON produkte.id = bestellpositionen.produkte_id
                        WHERE
                            DATE(bestellungen.timestamp_beendet) = :datum AND
                            (UNIX_TIMESTAMP(bestellungen.timestamp_beendet) DIV 900) - (UNIX_TIMESTAMP(DATE(bestellungen.timestamp_beendet)) DIV 900) = :quarter
                        GROUP BY
                            hour, quarter
                    "
                );

                $sth->bindParam(':datum', $result[$i]['datum'], PDO::PARAM_STR);
                $sth->bindParam(':quarter', $j, PDO::PARAM_INT);
                $sth->execute();

                if ($st = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $st['anzahl_bestellungen'] = intval($st['anzahl_bestellungen']);
                    $st['bestellung_alle_x_sekunden'] = floatval($st['bestellung_alle_x_sekunden']);
                    $st['bestellung_frequenz_mHz'] = floatval($st['bestellung_frequenz_mHz']);
                    $st['bestellte_produkte'] = intval($st['bestellte_produkte']);
                    $st['summe'] = floatval($st['summe']);
                    $quaters[$j] = $st;
                } else {
                    $quaters[$j] = [
                        "quarter" => $j,
                        "bestellte_produkte" => 0,
                        "summe" => 0,
                        "hour" => floor($j / 4),
                        "anzahl_bestellungen" => 0,
                        "bestellung_alle_x_sekunden" => 0,
                        "bestellung_frequenz_mHz" => 0
                    ];
                }

                $quaters[$j]['label'] = str_pad("" . $quaters[$j]['hour'], 2, '0', STR_PAD_LEFT) . ":" . str_pad("" . ($quaters[$j]['quarter'] % 4) * 15, 2, '0', STR_PAD_LEFT);
            }

            array_push($data, [
                "datum" => $result[$i]['datum'],
                "quaters" => $quaters
            ]);
        }

        return $data;
    }

    public function kennzahlen()
    {
        $data = [
            'taeglich' => [],
            'gesamt'=> [
                'bestellungen_anzahl' => 0,
                'produkte_anzahl' => 0,
                'umsatz' => 0
            ]
        ];

        $sth = $this->db->prepare(
            "SELECT
                    COUNT(DISTINCT(bestellungen.id)) AS bestellungen_anzahl,
                    SUM(bestellpositionen.anzahl) AS produkte_anzahl,
                    REPLACE(SUM(produkte.preis * bestellpositionen.anzahl), ',', '') AS umsatz,
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellpositionen
                LEFT JOIN
                    bestellungen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                GROUP BY
                    datum
                ORDER BY
                    datum DESC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $dataset['bestellungen_anzahl'] = intval($dataset['bestellungen_anzahl']);
            $dataset['produkte_anzahl'] = intval($dataset['produkte_anzahl']);
            $dataset['umsatz'] = floatval($dataset['umsatz']);
            $dataset['label'] = date_format(date_create($dataset['datum']), "d.m.Y");
            $dataset['hauptspeisen_anzahl'] = 0;
            $dataset['bons_anzahl'] = 0;

            $data['gesamt']['bestellungen_anzahl'] += $dataset['bestellungen_anzahl'];
            $data['gesamt']['produkte_anzahl'] += $dataset['produkte_anzahl'];
            $data['gesamt']['umsatz'] += $dataset['umsatz'];
            $data['gesamt']['hauptspeisen_anzahl'] = 0;
            $data['gesamt']['bons_anzahl'] = 0;

            $data['taeglich']["{$dataset['datum']}"] = $dataset;
        }

        // Hauptspeisen Ermittlung
        $sth = $this->db->prepare(
            "SELECT
                    SUM(bestellpositionen.anzahl) AS hauptspeisen_anzahl,
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellpositionen
                LEFT JOIN
                    bestellungen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                WHERE
                    produkte.hauptspeise = 1
                GROUP BY
                    datum
                ORDER BY
                    datum DESC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $data['taeglich']["{$dataset['datum']}"]['hauptspeisen_anzahl'] = intval($dataset['hauptspeisen_anzahl']);
            $data['gesamt']['hauptspeisen_anzahl'] += $data['taeglich']["{$dataset['datum']}"]['hauptspeisen_anzahl'];
        }

        // Bons Ermittlung
        $sth = $this->db->prepare(
            "SELECT
                    COUNT(bons_druck.id) AS bons_anzahl,
                    DATE(bons_druck.timestamp) AS datum
                FROM
                    bons_druck
                WHERE
                    bons_druck.success = 1
                GROUP BY
                    DATE(bons_druck.timestamp)
                ORDER BY
                    DATE(bons_druck.timestamp) DESC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $data['taeglich']["{$dataset['datum']}"]['bons_anzahl'] = intval($dataset['bons_anzahl']);
            $data['gesamt']['bons_anzahl'] += $data['taeglich']["{$dataset['datum']}"]['bons_anzahl'];
        }

        $data['taeglich'] = array_values($data['taeglich']);

        return $data;
    }

    public function produktbereiche()
    {
        $sth = $this->db->prepare(
            "SELECT
                    id,
                    name
                FROM
                    produktbereiche
                ORDER BY
                    id ASC"
        );
        $sth->execute();

        $data = [
            "header" => $sth->fetchAll(PDO::FETCH_ASSOC),
            "data" => []
        ];

        $sth = $this->db->prepare(
            "SELECT
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellungen
                GROUP BY
                    datum
                ORDER BY
                    datum ASC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $data["data"][$dataset['datum']] = [
                "datum" => $dataset['datum'],
                "data" => [],
                "summe" => 0.0
            ];

            foreach ($data['header'] as $header) {
                $data["data"][$dataset['datum']]['data']["_{$header['id']}"] = [
                    "bestellte_produkte" => 0,
                    "summe" => 0.0
                ];
            }
        }

        $sth = $this->db->prepare(
            "SELECT
                    REPLACE(SUM(bestellpositionen.anzahl), ',', '') AS bestellte_produkte,
                    REPLACE(SUM(produkte.preis * bestellpositionen.anzahl), ',', '') AS summe,
                    DATE(bestellungen.timestamp_beendet) AS datum,
                    produktbereiche.id AS produktbereiche_id
                FROM
                    bestellpositionen
                LEFT JOIN
                    bestellungen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                LEFT JOIN
                    produkteinteilungen ON produkteinteilungen.id = produkte.produkteinteilungen_id
                LEFT JOIN
                    produktkategorien ON produktkategorien.id = produkteinteilungen.produktkategorien_id
                LEFT JOIN
                    produktbereiche ON produktbereiche.id = produktkategorien.produktbereiche_id
                GROUP BY
                    datum, produktbereiche.id
                ORDER BY
                    datum ASC, produktbereiche.id ASC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $datum = $dataset['datum'];
            $produktbereiche_id = $dataset['produktbereiche_id'];

            $data["data"][$datum]['data']["_$produktbereiche_id"] = [
                "bestellte_produkte" => intval($dataset['bestellte_produkte']),
                "summe" => floatval($dataset['summe'])
            ];
        }

        foreach ($data["data"] as $datum) {
            $data["data"][$datum['datum']]["data"] = array_values($datum["data"]);
            foreach ($datum["data"] as $item) {
                $data["data"][$datum['datum']]["summe"] += $item["summe"];
            }
        }

        $data["data"] = array_values($data["data"]);

        return $data;
    }

    public function produktkategorien()
    {
        $sth = $this->db->prepare(
            "SELECT
                    id,
                    name
                FROM
                    produktkategorien
                ORDER BY
                    id ASC"
        );
        $sth->execute();

        $data = [
            "header" => $sth->fetchAll(PDO::FETCH_ASSOC),
            "data" => []
        ];

        $sth = $this->db->prepare(
            "SELECT
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellungen
                GROUP BY
                    datum
                ORDER BY
                    datum ASC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $data["data"][$dataset['datum']] = [
                "datum" => $dataset['datum'],
                "data" => [],
                "summe" => 0.0
            ];

            foreach ($data['header'] as $header) {
                $data["data"][$dataset['datum']]['data']["_{$header['id']}"] = [
                    "bestellte_produkte" => 0,
                    "summe" => 0.0
                ];
            }
        }

        $sth = $this->db->prepare(
            "SELECT
                    REPLACE(SUM(bestellpositionen.anzahl), ',', '') AS bestellte_produkte,
                    REPLACE(SUM(produkte.preis * bestellpositionen.anzahl), ',', '') AS summe,
                    DATE(bestellungen.timestamp_beendet) AS datum,
                    produktkategorien.id AS produktkategorien_id
                FROM
                    bestellpositionen
                LEFT JOIN
                    bestellungen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                LEFT JOIN
                    produkteinteilungen ON produkteinteilungen.id = produkte.produkteinteilungen_id
                LEFT JOIN
                    produktkategorien ON produktkategorien.id = produkteinteilungen.produktkategorien_id
                GROUP BY
                    datum, produktkategorien.id
                ORDER BY
                    datum ASC, produktkategorien.id ASC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $datum = $dataset['datum'];
            $produktkategorien_id = $dataset['produktkategorien_id'];

            $data["data"][$datum]['data']["_$produktkategorien_id"] = [
                "bestellte_produkte" => intval($dataset['bestellte_produkte']),
                "summe" => floatval($dataset['summe'])
            ];
        }

        foreach ($data["data"] as $datum) {
            $data["data"][$datum['datum']]["data"] = array_values($datum["data"]);
            foreach ($datum["data"] as $item) {
                $data["data"][$datum['datum']]["summe"] += $item["summe"];
            }
        }

        $data["data"] = array_values($data["data"]);

        return $data;
    }



    public function produkte($limit = 50)
    {

        $sth = $this->db->prepare(
            "SELECT
                    DATE(bestellungen.timestamp_beendet) AS datum
                FROM
                    bestellungen
                GROUP BY
                    datum
                ORDER BY
                    datum ASC"
        );
        $sth->execute();

        $data = [
            "header" => $sth->fetchAll(PDO::FETCH_ASSOC),
            "data" => []
        ];

        $sth = $this->db->prepare(
            "SELECT
                    produkte.id,
                    produkte.name,
                    SUM(bestellpositionen.anzahl) AS anzahl
                FROM
                    produkte
                LEFT JOIN
                    bestellpositionen ON bestellpositionen.produkte_id = produkte.id
                GROUP BY
                    produkte.id
                ORDER BY
                    anzahl DESC
                LIMIT 
                    :limit"
        );
        $sth->bindParam(':limit', $limit, PDO::PARAM_INT);
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $data["data"]["_{$dataset['id']}"] = [
                "id" => $dataset['id'],
                "produkt" => $dataset['name'],
                "data" => [],
                "anzahl" => 0,
                "umsatz" => 0.0
            ];

            foreach ($data['header'] as $header) {
                $data["data"]["_{$dataset['id']}"]['data'][$header['datum']] = [
                    "datum" => $header['datum'],
                    "anzahl" => 0,
                    "umsatz" => 0.0
                ];
            }
        }

        $sth = $this->db->prepare(
            "SELECT
                    REPLACE(SUM(bestellpositionen.anzahl), ',', '') AS bestellte_produkte,
                    REPLACE(SUM(produkte.preis * bestellpositionen.anzahl), ',', '') AS summe,
                    DATE(bestellungen.timestamp_beendet) AS datum,
                    produkte.id AS produkte_id
                FROM
                    bestellpositionen
                LEFT JOIN
                    bestellungen ON bestellpositionen.bestellungen_id = bestellungen.id
                LEFT JOIN
                    produkte ON produkte.id = bestellpositionen.produkte_id
                GROUP BY
                    datum, produkte.id
                ORDER BY
                    datum ASC, produkte.id ASC"
        );
        $sth->execute();

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $dataset) {
            $datum = $dataset['datum'];
            $produkte_id = $dataset['produkte_id'];

            if (isset($data["data"]["_$produkte_id"])){
                $data["data"]["_$produkte_id"]['data'][$datum]["anzahl"] = intval($dataset['bestellte_produkte']);
                $data["data"]["_$produkte_id"]['data'][$datum]["umsatz"] = floatval($dataset['summe']);
            }
        }

        foreach ($data["data"] as $produkt) {
            $data["data"]["_{$produkt['id']}"]["data"] = array_values($produkt["data"]);
            foreach ($produkt["data"] as $item) {
                $data["data"]["_{$produkt['id']}"]["anzahl"] += $item["anzahl"];
                $data["data"]["_{$produkt['id']}"]["umsatz"] += $item["umsatz"];
            }
        }

        $data["data"] = array_values($data["data"]);

        return $data;
    }
}
