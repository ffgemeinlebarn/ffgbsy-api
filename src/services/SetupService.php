<?php

declare(strict_types=1);

namespace FFGBSY\Services;

final class SetupService extends BaseService
{
    public function setupDatabase()
    {
        $sql = file_get_contents(__DIR__ . "/../../database/structure.sql");
        $sth = $this->db->prepare($sql);

        return $sth->execute();
    }

    public function seedData()
    {
        $sql = file_get_contents(__DIR__ . "/../../database/empty-fest-seed.sql");
        $sth = $this->db->prepare($sql);

        return $sth->execute();
    }
}
