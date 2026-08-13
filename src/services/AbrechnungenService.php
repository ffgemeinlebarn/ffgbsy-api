<?php

declare(strict_types=1);

namespace FFGBSY\Services;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use FFGBSY\Services\PersonenService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\BonsService;

final class AbrechnungenService extends BaseService
{
    private PersonenService $personenService;
    private BestellpositionenService $bestellpositionenService;
    private BonsService $bonsService;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->personenService = $container->get('personen');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->bonsService = $container->get('bons');
        parent::__construct($container, $logger);
    }

    // Overviews - Kellner & Offene Summen

    public function readOverview($stelle, $id = null)
    {
        if ($id != null) {
            $person = $this->personenService->read($id);

            if ($person === null) {
                return null;
            }

            $sth = $this->db->prepare("
                SELECT
                    COALESCE(SUM(summe), 0) AS summe,
                    COUNT(id) AS anzahl
                FROM abrechnungen
                WHERE stelle = :stelle AND kellner_id = :id
            ");
            $sth->bindValue(':stelle', $stelle, PDO::PARAM_STR);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            $abrechnungen = $sth->fetch(PDO::FETCH_OBJ);

            $sth = $this->db->prepare("
                SELECT
                    COALESCE(SUM(summe), 0) AS summe,
                    COUNT(id) AS anzahl
                FROM rueckrechnungen
                WHERE stelle = :stelle AND kellner_id = :id
            ");
            $sth->bindValue(':stelle', $stelle, PDO::PARAM_STR);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            $rueckrechnungen = $sth->fetch(PDO::FETCH_OBJ);

            $obj = new \stdClass();
            $obj->kellner = $person;
            $obj->abrechnungen_anzahl = (int) $abrechnungen->anzahl;
            $obj->abrechnungen_summe = (float) $abrechnungen->summe;
            $obj->rueckrechnungen_anzahl = (int) $rueckrechnungen->anzahl;
            $obj->rueckrechnungen_summe = (float) $rueckrechnungen->summe;
            $obj->summe_offen = (float) decimalSub($abrechnungen->summe, $rueckrechnungen->summe, 2);

            return $obj;
        } else {
            $personen = $this->personenService->read();

            // Aggregation für ALLE kellner_id auf einmal
            $sth = $this->db->prepare("
                SELECT kellner_id, COALESCE(SUM(summe), 0) AS summe, COUNT(id) AS anzahl
                FROM abrechnungen
                WHERE stelle = :stelle
                GROUP BY kellner_id
            ");
            $sth->bindValue(':stelle', $stelle, PDO::PARAM_STR);
            $sth->execute();
            $abrechnungen = $sth->fetchAll(PDO::FETCH_OBJ);

            if ($abrechnungen == false) {
                $abrechnungen = [];
            }

            $sth = $this->db->prepare("
                SELECT kellner_id, COALESCE(SUM(summe), 0) AS summe, COUNT(id) AS anzahl
                FROM rueckrechnungen
                WHERE stelle = :stelle
                GROUP BY kellner_id
            ");
            $sth->bindValue(':stelle', $stelle, PDO::PARAM_STR);
            $sth->execute();
            $rueckrechnungen = $sth->fetchAll(PDO::FETCH_OBJ);

            if ($rueckrechnungen == false) {
                $rueckrechnungen = [];
            }

            // Nach kellner_id indizieren für O(1)-Zugriff
            $abrechnungenByKellner = [];
            foreach ($abrechnungen as $row) {
                $abrechnungenByKellner[$row->kellner_id] = $row;
            }

            $rueckrechnungenByKellner = [];
            foreach ($rueckrechnungen as $row) {
                $rueckrechnungenByKellner[$row->kellner_id] = $row;
            }

            $result = [];
            foreach ($personen as $person) {
                $obj = new \stdClass();
                $obj->kellner = $person;

                $a = $abrechnungenByKellner[$person->id] ?? null;
                $r = $rueckrechnungenByKellner[$person->id] ?? null;

                $obj->abrechnungen_anzahl = $a ? (int) $a->anzahl : 0;
                $obj->abrechnungen_summe = $a ? (float) $a->summe : 0.0;
                $obj->rueckrechnungen_anzahl = $r ? (int) $r->anzahl : 0;
                $obj->rueckrechnungen_summe = $r ? (float) $r->summe : 0.0;
                $obj->summe_offen = (float) decimalSub($a->summe ?? "0.0", $r->summe ?? "0.0", 2);

                if ($obj->abrechnungen_anzahl > 0 || $person->kellner) {
                    $result[] = $obj;
                }

                usort($result, fn($a, $b) => $a->summe_offen < $b->summe_offen ? 1 : -1);
            }

            return $result;
        }
    }

    // Abrechnungen

    public function createAbrechnung($data)
    {
        $gesamtsumme = 0.0;

        foreach ($data['bons'] as $bon) {
            $sth = $this->db->prepare("
                SELECT
                    SUM(bp.anzahl * (pr.preis + IFNULL(eig_sum_mit.extra_preis, 0) - IFNULL(eig_sum_ohne.extra_preis, 0))) AS summe
                FROM bons_bestellpositionen bbp
                JOIN bestellpositionen bp
                    ON bp.id = bbp.bestellpositionen_id
                JOIN produkte pr
                    ON pr.id = bp.produkte_id
                LEFT JOIN (
                    SELECT
                        bpe.bestellpositionen_id,
                        SUM(eig.preis) AS extra_preis
                    FROM bestellpositionen_eigenschaften bpe
                    JOIN eigenschaften eig ON eig.id = bpe.eigenschaften_id
                    WHERE bpe.aktiv = 1 AND bpe.in_produkt_enthalten = 0
                    GROUP BY bpe.bestellpositionen_id
                ) eig_sum_mit
                    ON eig_sum_mit.bestellpositionen_id = bp.id
                LEFT JOIN (
                    SELECT
                        bpe.bestellpositionen_id,
                        SUM(eig.preis) AS extra_preis
                    FROM bestellpositionen_eigenschaften bpe
                    JOIN eigenschaften eig ON eig.id = bpe.eigenschaften_id
                    WHERE bpe.aktiv = 0 AND bpe.in_produkt_enthalten = 1
                    GROUP BY bpe.bestellpositionen_id
                ) eig_sum_ohne
                    ON eig_sum_ohne.bestellpositionen_id = bp.id
                WHERE bbp.bons_id = :bons_id
            ");
            $sth->execute(['bons_id' => $bon['id']]);
            $bonSumme = (float) $sth->fetchColumn();

            if ($bon['type'] == "storno") {
                $gesamtsumme -= $bonSumme;
            } else {
                $gesamtsumme += $bonSumme;
            }
        }

        $strSum = "{$gesamtsumme}";

        $sth = $this->db->prepare("INSERT INTO abrechnungen (stelle, kellner_id, summe) VALUES (:stelle, :kellner_id, :summe)");
        $sth->bindParam(':stelle', $data['stelle'], PDO::PARAM_STR);
        $sth->bindParam(':kellner_id', $data['kellner']['id'], PDO::PARAM_INT);
        $sth->bindParam(':summe', $strSum, PDO::PARAM_STR);
        $sth->execute();

        return $this->readKellnerStatus($data['kellner']['id']);
    }

    // Rückrechnungen

    public function createRueckrechnung($data)
    {
        $sth = $this->db->prepare("INSERT INTO rueckrechnungen (stelle, kellner_id, summe) VALUES (:stelle, :kellner_id, :summe)");
        $sth->bindParam(':stelle', $data['stelle'], PDO::PARAM_STR);
        $sth->bindParam(':kellner_id', $data['kellner']['id'], PDO::PARAM_INT);
        $sth->bindParam(':summe', $data['summe'], PDO::PARAM_STR);
        $sth->execute();

        return $this->readKellnerStatus($data['kellner']['id']);
    }

    public function readKellnerStatus($id)
    {
        $sthAbrechnungen = $this->db->prepare("SELECT * FROM abrechnungen WHERE kellner_id = :kellner_id");
        $sthAbrechnungen->bindParam(':kellner_id', $id, PDO::PARAM_INT);

        $sthRueckrechnungen = $this->db->prepare("SELECT * FROM rueckrechnungen WHERE kellner_id = :kellner_id");
        $sthRueckrechnungen->bindParam(':kellner_id', $id, PDO::PARAM_INT);

        $sth = $this->db->prepare("
            SELECT
                (SELECT COALESCE(SUM(a.summe), 0) FROM abrechnungen a WHERE a.kellner_id = :kellner_id) AS abrechnungen_summe,
                (SELECT COALESCE(SUM(r.summe), 0) FROM rueckrechnungen r WHERE r.kellner_id = :kellner_id) AS rueckrechnungen_summe
        ");
        $sth->bindParam(':kellner_id', $id, PDO::PARAM_INT);
        $sth->execute();
        $data = $sth->fetch(PDO::FETCH_OBJ);

        $data->kellner = $this->personenService->read($id);
        $data->abrechnungen = $this->multiRead($sthAbrechnungen);
        $data->rueckrechnungen = $this->multiRead($sthRueckrechnungen);

        $data->summe_offen = (float) decimalSub($data->abrechnungen_summe, $data->rueckrechnungen_summe, 2);
        $data->abrechnungen_summe = (float) $data->abrechnungen_summe;
        $data->rueckrechnungen_summe = (float) $data->rueckrechnungen_summe;

        return $data;
    }

    public function deleteAbrechnung($id)
    {
        $sth = $this->db->prepare("DELETE FROM abrechnungen WHERE id = :id");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        return $sth->execute();
    }

    public function deleteRueckrechnung($id)
    {
        $sth = $this->db->prepare("DELETE FROM rueckrechnungen WHERE id = :id");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        return $sth->execute();
    }

    protected function singleMap($obj)
    {
        $obj->id = $this->asNumber($obj->id);
        $obj->summe = $this->asDecimal($obj->summe);

        return $obj;
    }
}

function decimalSub(string $a, string $b, int $scale = 2): string
{
    $factor = 10 ** $scale;

    // String-Decimal in Integer (z. B. "123.45" -> 12345) umwandeln,
    // ohne über float zu gehen
    $toInt = function (string $val) use ($factor, $scale) {
        $val = bcround_free_normalize($val, $scale); // siehe unten
        [$intPart, $fracPart] = array_pad(explode('.', $val, 2), 2, '0');
        $fracPart = str_pad(substr($fracPart, 0, $scale), $scale, '0');
        $sign = str_starts_with($intPart, '-') ? -1 : 1;
        $intPart = ltrim($intPart, '-');
        return $sign * ((int) $intPart * $factor + (int) $fracPart);
    };

    $result = $toInt($a) - $toInt($b);

    $sign = $result < 0 ? '-' : '';
    $result = abs($result);
    $intPart = intdiv($result, $factor);
    $fracPart = str_pad((string) ($result % $factor), $scale, '0', STR_PAD_LEFT);

    return $sign . $intPart . '.' . $fracPart;
}

function bcround_free_normalize(string $val, int $scale): string
{
    // stellt sicher, dass immer ein Dezimalpunkt vorhanden ist
    return str_contains($val, '.') ? $val : $val . '.' . str_repeat('0', $scale);
}
