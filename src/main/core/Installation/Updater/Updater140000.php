<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater140000 extends Updater
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function preUpdate()
    {
        // the namespace of the migrations has changed (eg. removed the `pdo_mysql` part)
        // Doctrine will try to re-execute migrations because of the renaming
        // we need to update the version classnames in the DB to avoid breaking updates
        $this->log('Updating doctrine migration versions...');
        // retrieve all doctrine versions tables
        $stmt = $this->connection->prepare('
            SHOW TABLES LIKE "doctrine_%_versions"
        ');

        $results = $stmt->executeQuery();
        $tables = $results->fetchFirstColumn();

        foreach ($tables as $table) {
            $this->log(sprintf('Updating doctrine migration versions %s...', $table));

            // update last version execution
            $versionsQuery = $this->connection->prepare("
                SELECT * FROM {$table} ORDER BY version ASC
            ");

            $results = $versionsQuery->executeQuery();
            $versions = $results->fetchAllAssociative();

            foreach ($versions as $version) {
                $className = $version['version'];
                if (!class_exists($version['version']) && false !== strpos($version['version'], 'pdo_mysql')) {
                    $className = str_replace('\\pdo_mysql', '', $version['version']);
                }

                if (!class_exists($className)) {
                    // migration version has been removed, we can remove it from the table
                    $deleteQuery = $this->connection->prepare("
                        DELETE FROM {$table} 
                        WHERE `version` = :version
                    ");

                    $deleteQuery->executeQuery(['version' => $version['version']]);
                } elseif ($version['version'] !== $className) {
                    // migration version has been renamed
                    $updateVersionQuery = $this->connection->prepare("
                        UPDATE {$table} SET `version` = :updatedVersion WHERE version = :version
                    ");

                    $updateVersionQuery->executeQuery([
                        'version' => $version['version'],
                        'updatedVersion' => $className,
                    ]);
                }
            }

            /*$lastVersion = array_pop($versions);
            if ($lastVersion) {
                // clean table (we only need to keep the last version)
                $deleteQuery = $this->connection->prepare("
                    DELETE FROM {$table}
                    WHERE `version` != :lastVersion
                ");

                $deleteQuery->executeQuery(['lastVersion' => $lastVersion['version']]);

                if (false !== strpos($lastVersion['version'], 'pdo_mysql') && !class_exists($lastVersion['version'])) {
                    // version has been renamed
                    $updateVersionQuery = $this->connection->prepare("
                        UPDATE {$table} SET `version` = :updatedVersion WHERE version = :version
                    ");

                    $updateVersionQuery->executeQuery([
                        'version' => $lastVersion['version'],
                        'updatedVersion' => str_replace('\\pdo_mysql', '', $lastVersion['version']),
                    ]);
                }
            }*/
        }
    }
}
