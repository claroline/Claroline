<?php

namespace Claroline\CoreBundle\Library\Migration;

use Doctrine\DBAL\Connection;

trait ConditionalMigrationTrait
{
    private function checkTableExists(String $table, Connection $connection): bool
    {
        try {
            $connection->executeQuery("SELECT 1 FROM $table");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
