<?php

declare(strict_types=1);

namespace FFGBSY\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FFGBSY\Services\ProdukteService;
use FFGBSY\Services\ProdukteinteilungenService;
use FFGBSY\Services\BestellungenService;
use FFGBSY\Services\EigenschaftenService;
use FFGBSY\Services\BestellpositionenService;
use FFGBSY\Services\BonsService;

final class TestsController extends BaseController
{
    private ProdukteService $produkteService;
    private ProdukteinteilungenService $produkteinteilungenService;
    private BestellungenService $bestellungenService;
    private BestellpositionenService $bestellpositionenService;
    private EigenschaftenService $eigenschaftenService;
    private BonsService $bonsService;

    public function __construct(ContainerInterface $container)
    {
        $this->produkteService = $container->get('produkte');
        $this->produkteinteilungenService = $container->get('produkteinteilungen');
        $this->bestellungenService = $container->get('bestellungen');
        $this->bestellpositionenService = $container->get('bestellpositionen');
        $this->eigenschaftenService = $container->get('eigenschaften');
        $this->bonsService = $container->get('bons');
    }

    public function randomBestellung(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $bonDruck = $input['bonDruck'];

        $einteilungen = array(
            array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 30, 31, 32, 33, 34),
            array(20, 22, 23, 24, 26),
            array(25),
            array(27, 28, 29),
            array(21, 23)
        );
            
        $bestellung = $this->bestellungenService->create([
            "tisch" => ["id" => rand(1, 74)],
            "timestamp_begonnen" => date('Y-m-d H:i:s'),
            "aufnehmer" => ["id" => rand(1, 14)],
            "device_name" => "Stresstest",
            "device_ip" => "127.0.0.1",
            "bestellpositionen" => [ ]
        ]);

        foreach($einteilungen as $einteilung)
        {
            $id1 = $einteilung[array_rand($einteilung)];
            $id2 = $einteilung[array_rand($einteilung)];
            
            $produkte = $this->produkteService->readByProdukteinteilung($id1);
            $produkt1 = $produkte[array_rand($produkte)];
            $produkte = $this->produkteService->readByProdukteinteilung($id2);
            $produkt2 = $produkte[array_rand($produkte)];

            $produkt1->eigenschaften = $this->eigenschaftenService->readAllByProduktNested($produkt1->id);
            $produkt2->eigenschaften = $this->eigenschaftenService->readAllByProduktNested($produkt2->id);

            $bestellposition1 = $this->bestellpositionenService->addToBestellung($bestellung->id, [
                "anzahl" => rand(1, 5),
                "produkt" => (array) $produkt1,
                "notiz" => null
            ]);

            foreach($produkt1->eigenschaften as $e)
            {
                $e->aktiv = !$e->aktiv;
                $this->eigenschaftenService->addToBestellposition($bestellposition1->id, (array) $e);
            }

            $bestellposition2 = $this->bestellpositionenService->addToBestellung($bestellung->id, [
                "anzahl" => rand(1, 5),
                "produkt" => (array) $produkt2,
                "notiz" => null
            ]);

            foreach($produkt2->eigenschaften as $e)
            {
                $e->aktiv = !$e->aktiv;
                $this->eigenschaftenService->addToBestellposition($bestellposition2->id, (array) $e);
            }
        }

        $affectedDruckerIds = $this->bonsService->getAffectedDruckerIdsForBestellung('bestell', $bestellung->id);
        foreach($affectedDruckerIds as $druckerId)
        {
            $this->bonsService->create([
                "type" => "bestell",
                "bestellungen_id" => $bestellung->id,
                "drucker_id" => $druckerId,
                "bestellpositionen" => $this->bestellpositionenService->readByTypeAndBestellungAndDrucker('bestell', $bestellung->id, $druckerId)
            ]);
        }

        $bestellung = $this->bestellungenService->read($bestellung->id);
        $bestellbons = (array) $bestellung->bestellbons;

        $bons = [];
        foreach($bestellbons as $bestellbon)
        {
            $bestellbon = (array) $bestellbon;
            $bestellbon['drucker'] = (array) $bestellbon['drucker'];
            array_push($bons, $bestellbon);
        }


        $data = $this->bonsService->printMultiple($bons);

        return $this->responseAsJson($response, $data);
    }
}
