<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\DatabaseBackup;
use Psr\Log\LogLevel;

class DatabaseManager
{
    use LoggableTrait;

    private $kernel;

    public function __construct(ObjectManager $om, $conn, FinderProvider $finder, $archiveDir)
    {
        $this->om = $om;
        $this->conn = $conn;
        $this->finder = $finder;
        $this->archiveDir = $archiveDir;
    }

    public function backupRows($class, $searches, $tableName, $batch = null, $selfRemove = false, $dumpCsv = true)
    {
        $query = $this->finder->get($class)->find($searches, null, 0, -1, false, [Options::SQL_QUERY]);
        $name = '_bkp_'.$tableName.'_'.$batch;
        $table = $this->om->getMetadataFactory()->getMetadataFor($class)->getTableName();

        if ($dumpCsv) {
            $this->dumpCsv($class, $searches, $tableName.'_'.$batch);
        }

        try {
            $this->log("backing up $class in $name...");
            $this->createBackupFromQuery($name, $this->finder->get($class)->getSqlWithParameters($query), $table, DatabaseBackup::TYPE_PARTIAL, $batch);
        } catch (\Exception $e) {
            $this->log("Couldn't backup $class".$e->getMessage(), LogLevel::ERROR);
        }

        if ($selfRemove) {
            $this->log('Removing rows...');
            $this->finder->get($class)->delete($searches);
        }
    }

    public function dumpCsv($class, $searches, $name)
    {
        $path = $this->archiveDir.DIRECTORY_SEPARATOR.$name.'.csv';
        $query = $this->finder->get($class)->find($searches, null, 0, -1, false, [Options::SQL_QUERY]);
        $sql = $this->finder->get($class)->getSqlWithParameters($query);
        $rows = $this->conn->query($sql);
        $fp = fopen($path, 'w');

        $firstRow = $rows->fetch();

        if (is_array($firstRow)) {
            fputcsv($fp, array_keys($firstRow));
            fputcsv($fp, $firstRow);

            while ($row = $rows->fetch()) {
                fputcsv($fp, $row);
            }
        }

        fclose($fp);
    }

    public function backupTables($tables)
    {
        foreach ($tables as $table) {
            $name = '_bkp_'.$table.'_'.time();

            try {
                $this->log("backing up $table as $name...");
                $this->createBackupFromQuery($name, "SELECT * FROM $table", $table);
            } catch (\Exception $e) {
                $this->log("Couldn't backup $table ".$e->getMessage(), LogLevel::ERROR);
            }
        }
    }

    private function createBackupFromQuery($name, $query, $table, $type = DatabaseBackup::TYPE_FULL, $batch = null)
    {
        $this->conn->query("CREATE TABLE $name AS ($query)");
        $dbBackup = new DatabaseBackup();
        $dbBackup->setName($name);
        $dbBackup->setTable($table);
        $dbBackup->setType($type);
        $dbBackup->setBatch($batch);
        $this->om->persist($dbBackup);
        $this->om->flush();
    }

    public function dropTables($tables, $backup = false)
    {
        if ($backup) {
            $this->backupTables($tables);
        }

        foreach ($tables as $table) {
            try {
                $this->log('DROP '.$table);
                $sql = '
                        SET FOREIGN_KEY_CHECKS=0;
                        DROP TABLE '.$table.';
                        SET FOREIGN_KEY_CHECKS=1;
                    ';

                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            } catch (\Exception $e) {
                $this->log('Couldnt drop '.$table.' '.$e->getMessage());
            }
        }
    }
}
