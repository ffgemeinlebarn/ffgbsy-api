<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use FFGBSY\Services\ProdukteinteilungenService;
    use FFGBSY\Services\ProdukteService;
    use FFGBSY\Services\EigenschaftenService;

    final class ProduktkategorienService extends BaseService
    {
        private ProdukteinteilungenService $produkteinteilungenService;
        private ProdukteService $produkteService;
        private EigenschaftenService $eigenschaftenService;

        public function __construct(ContainerInterface $container)
        {
            $this->produkteinteilungenService = $container->get('produkteinteilungen');
            $this->produkteService = $container->get('produkte');
            $this->eigenschaftenService = $container->get('eigenschaften');
            parent::__construct($container);
        }

        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO produktkategorien (name, color, produktbereiche_id, drucker_id_level_1, sortierindex) VALUES (:name, :color, :produktbereiche_id, :drucker_id_level_1, :sortierindex)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':color', $data['color'], PDO::PARAM_STR);
            $sth->bindParam(':produktbereiche_id', $data['produktbereich']['id'], PDO::PARAM_INT);
            $sth->bindParam(':drucker_id_level_1', $data['drucker_id_level_1'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM produktkategorien WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM produktkategorien");
                return $this->multiRead($sth);
            }
        }

        public function readAllNested()
        {
            $sth = $this->db->prepare("SELECT * FROM produktkategorien");
            $produktkategorien = $this->multiRead($sth);

            foreach($produktkategorien as $produktkategorie)
            {
                $produktkategorie->produkteinteilungen = $this->produkteinteilungenService->readByProduktkategorie($produktkategorie->id);

                foreach($produktkategorie->produkteinteilungen as $produkteinteilung)
                {
                    $produkteinteilung->produkte = $this->produkteService->readByProdukteinteilung($produkteinteilung->id);

                    foreach($produkteinteilung->produkte as $produkt)
                    {
                        $produkt->eigenschaften = $this->eigenschaftenService->readAllByProduktAndProduktkategorie($produkt->id, $produktkategorie->id);
                    }
                }
            }

            return $produktkategorien;
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE produktkategorien SET name=:name, color=:color, produktbereiche_id=:produktbereiche_id, drucker_id_level_1=:drucker_id_level_1, sortierindex=:sortierindex WHERE id=:id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':color', $data['color'], PDO::PARAM_STR);
            $sth->bindParam(':produktbereiche_id', $data['produktbereich']['id'], PDO::PARAM_INT);
            $sth->bindParam(':drucker_id_level_1', $data['drucker_id_level_1'], PDO::PARAM_INT);
            $sth->bindParam(':sortierindex', $data['sortierindex'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM produktkategorien WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->drucker_id_level_1 = $this->asNumberOrNull($obj->drucker_id_level_1);
            $obj->produktbereiche_id = $this->asNumber($obj->produktbereiche_id);
            $obj->sortierindex = $this->asNumber($obj->sortierindex);
            return $obj;
        }
    }
