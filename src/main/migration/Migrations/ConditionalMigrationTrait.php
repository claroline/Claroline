<?php

namespace Claroline\MigrationBundle\Migrations;

use Doctrine\DBAL\Connection;

trait ConditionalMigrationTrait
{
    private function checkTableExists(string $table, Connection $connection): bool
    {
        try {
            $connection->executeQuery("SELECT 1 FROM $table");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkForeignKeyExists(string $keyName, Connection $connection): bool
    {
        $stmt = $connection->executeQuery('
            SELECT constraint_name
            FROM information_schema.key_column_usage
            WHERE LOWER(constraint_name) = LOWER(:keyName)
              AND table_schema = :database
        ', [
            'database' => $connection->getDatabase(),
            'keyName' => $keyName,
        ]);

        return !empty($stmt->fetchAllAssociative());
    }

    private function checkColumnExists(string $tableName, string $columnName, Connection $connection): bool
    {
        $stmt = $connection->executeQuery('
            SELECT *
            FROM information_schema.columns
            WHERE table_name = :tableName AND column_name = :columnName
        ', [
            'tableName' => $tableName,
            'columnName' => $columnName,
        ]);

        return !empty($stmt->fetchAllAssociative());
    }
}
