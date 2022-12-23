<?php

namespace Claroline\LogBundle\Archive;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\LogBundle\Entity\AbstractLog;
use Claroline\LogBundle\Entity\Archive\SecurityLogArchive;
use Claroline\LogBundle\Repository\LogRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;

class LogRotator implements LogRotatorInterface
{
    private $connection;
    private $om;

    public function __construct(Connection $connection, ObjectManager $om)
    {
        $this->connection = $connection;
        $this->om = $om;
    }

    /**
     * @param \DateTimeInterface $to   The date until when log entries should be archived
     * @param class-string       $logType The FQCN of the log entity whose entries should be rotated
     */
    public function rotateLogs(\DateTimeInterface $to, string $logType): void
    {
        $logRepository = $this->om->getRepository($logType);

        if (!is_subclass_of($logRepository, LogRepositoryInterface::class)) {
            throw new \LogicException(sprintf('The log entity must have a repository implementing "%s", "%s" given.', LogRepositoryInterface::class, get_debug_type($logType)));
        }

        // Find all log entries created before $to

        /** @var AbstractLog $log */
        foreach ($qb->getQuery()->getResult() as $log) {

        }

        /** @var AbstractLog $oldestLog */
        $oldestLog = $logs->last();

        $archiveTableName = $logType::ARCHIVE_TABLE_PREFIX."{$to->format('Ymd')}-{$oldestLog->getDate()->format('Ymd')}";

        $findLastArchivedLogQuery = "SELECT date FROM {$lastArchiveTable} ORDER BY date DESC LIMIT 1";
        $date = ''; // fixme perform query
        $newArchiveTableName = SecurityLogArchive::ARCHIVE_TABLE_PREFIX.$now->getTimestamp();
        $createArchiveTableQuery = <<<SQL
CREATE TABLE 
SQL;

        // Create archive table (claro_log_archive_functionnal_{$now} https://stackoverflow.com/questions/30871721/doctrine2-dynamic-table-name-for-entity
        // Begin transaction
        // Fetch active logs where date between $endDate and $toDate (preconfigured period)
        // Move logs from active table to the archive table https://stackoverflow.com/questions/1612267/move-sql-data-from-one-table-to-another
        // Delete archived logs from active logs table

        // Add claro_log_archives table to keep a reference of all archives (filename, date from, date to)

    }

    private function getLastArchiveTable(string $type): ?string
    {
        $archiveTablePrefix = $type::ARCHIVE_TABLE_PREFIX;

        $archiveTables = array_filter(
            $this->connection->getSchemaManager()->listTableNames(),
            fn($tableName) => str_contains($tableName, $archiveTablePrefix)
        );

        $now = (new \DateTime)->getTimestamp();
        $mostRecentDate = 0;
        $lastArchiveTable = null;

        foreach ($archiveTables as $archiveTable) {
            $date = substr($archiveTable, strpos($archiveTable, \strlen($archiveTablePrefix) -1);
            $archiveTableDate = strtotime($date);

            if ($archiveTableDate > $mostRecentDate && $archiveTableDate < $now) {
                $mostRecentDate = $archiveTableDate;
                $lastArchiveTable = $archiveTable;
            }
        }

        return $lastArchiveTable ?? null;
    }
}