<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\DruckerService;
    use FFGBSY\Services\TischeService;
    use FFGBSY\Services\PrintService;
    use FFGBSY\Services\ProdukteService;

    final class CelebrationService extends BaseService
    {
        private DruckerService $druckerService;
        private TischeService $tischeService;
        private PrintService $printService;
        private ProdukteService $produkteService;

        public function __construct(ContainerInterface $container)
        {
            $this->druckerService = $container->get('drucker');
            $this->tischeService = $container->get('tische');
            $this->printService = $container->get('print');
            $this->produkteService = $container->get('produkte');
            parent::__construct($container);
        }

        public function invoke($produktId)
        {
            $step = 100;

            $sth = $this->db->prepare("SELECT celebration_active, celebration_last, celebration_prefix, celebration_suffix FROM produkte WHERE id = :id");
            $sth->bindParam(':id', $produktId, PDO::PARAM_INT);
            $sth->execute();
            $result = $sth->fetch(PDO::FETCH_ASSOC);

            if (filter_var($result['celebration_active'], FILTER_VALIDATE_BOOLEAN))
            {
                $lastCelebration = intval($result['celebration_last']);
                $celebrationPrefix = $result['celebration_prefix'];
                $celebrationSuffix = $result['celebration_suffix'];

                $sth = $this->db->prepare("SELECT SUM(anzahl) as anzahl FROM bestellpositionen WHERE produkte_id = :produkte_id");
                $sth->bindParam(':produkte_id', $produktId, PDO::PARAM_INT);
                $sth->execute();
                $anzahl = $sth->fetch(PDO::FETCH_ASSOC)['anzahl'];

                if (($lastCelebration + $step) < $anzahl)
                {
                    $celebration = $lastCelebration + $step;

                    $sth = $this->db->prepare("UPDATE produkte SET celebration_last = :celebration_last WHERE id = :id");
                    $sth->bindParam(':id', $produktId, PDO::PARAM_INT);
                    $sth->bindParam(':celebration_last', $celebration, PDO::PARAM_INT);
                    $sth->execute();

                    $sth = $this->db->prepare(
                        "SELECT 
                            produktbereiche.drucker_id_level_0,
                            produktkategorien.drucker_id_level_1,
                            produkte.drucker_id_level_2
                        FROM 
                            produkte
                        LEFT JOIN 
                            produkteinteilungen ON produkteinteilungen.id = produkte.produkteinteilungen_id
                        LEFT JOIN 
                            produktkategorien ON produktkategorien.id = produkteinteilungen.produktkategorien_id
                        LEFT JOIN 
                            produktbereiche ON produktbereiche.id = produktkategorien.produktbereiche_id
                        WHERE
                            produkte.id = :id"
                    );
                    $sth->bindParam(':id', $produktId, PDO::PARAM_INT);
                    $sth->execute();
                    $druckerIds = $sth->fetch(PDO::FETCH_ASSOC);

                    $druckerIds['drucker_id_level_0'] = $this->asNumberOrNull($druckerIds['drucker_id_level_0']);
                    $druckerIds['drucker_id_level_1'] = $this->asNumberOrNull($druckerIds['drucker_id_level_1']);
                    $druckerIds['drucker_id_level_2'] = $this->asNumberOrNull($druckerIds['drucker_id_level_2']);
                    
                    if ($druckerIds['drucker_id_level_2'] != null)
                    {
                        $drucker = $this->druckerService->read($druckerIds['drucker_id_level_2']);
                    }
                    else if ($druckerIds['drucker_id_level_1'] != null)
                    {
                        $drucker = $this->druckerService->read($druckerIds['drucker_id_level_1']);
                    }
                    else
                    {
                        $drucker = $this->druckerService->read($druckerIds['drucker_id_level_0']);
                    }

                    $produkt = $this->produkteService->read($produktId);
                    $setup = $this->printService->setupPrinter($drucker);
    
                    if ($setup->success)
                    {
                        if ($celebrationSuffix != null && count_chars($celebrationSuffix) > 0)
                        {
                            $celebrationSuffix = "$celebrationSuffix\n";
                        }

                        $printer = $setup->printer;
                        $this->printService->printCelebrationHeader($printer);
                        $this->printService->printCelebrationContent($printer, $celebration, $celebrationPrefix, "$celebrationSuffix{$produkt->name}", "../assets/celebration-{$drucker->id}.png");
                        $this->printService->printFinish($printer);
                    }
                }
            }

            return null;
        }
    }
