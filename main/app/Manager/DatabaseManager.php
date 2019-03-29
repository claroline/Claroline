<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\DatabaseBackup;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LogLevel;

/**
 * @DI\Service("claroline.manager.database_manager")
 */
class DatabaseManager
{
    use LoggableTrait;

    private $kernel;

    /**
     * @DI\InjectParams({
     *     "om"     = @DI\Inject("claroline.persistence.object_manager"),
     *     "conn"   = @DI\Inject("doctrine.dbal.default_connection"),
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     */
    public function __construct(ObjectManager $om, $conn, FinderProvider $finder)
    {
        $this->om = $om;
        $this->conn = $conn;
        $this->finder = $finder;
    }

    public function backupRows($class, $searches, $tableName, $batch = null)
    {
        $query = $this->finder->get($class)->find($searches, null, 0, -1, false, [Options::SQL_QUERY]);
        $name = '_bkp_'.$tableName.'_'.time();
        $table = $this->om->getMetadataFactory()->getMetadataFor($class)->getTableName();

        try {
            $this->log("backing up $class in $name...");
            $this->createBackupFromQuery($name, $this->finder->get($class)->getSqlWithParameters($query), $table, DatabaseBackup::TYPE_PARTIAL, $batch);
        } catch (\Exception $e) {
            $this->log("Couldn't backup $class".$e->getMessage(), LogLevel::ERROR);
        }
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
        $this->conn->query("
            CREATE TABLE $name AS ($query)
        ");
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
