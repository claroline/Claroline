<?php

namespace Claroline\MigrationBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;

class Generator
{
    const QUERIES_UP = 'up';
    const QUERIES_DOWN = 'down';

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // TODO :
    // remove hardcoded platform
    // return false if sync
    // keep generated schema in cache for susbsequent calls
    public function generateMigrationQueries(Bundle $bundle, array $platforms)
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $bundleTables = array();

        foreach ($metadata as $entityMetadata) {
            if (0 === strpos($entityMetadata->name, $bundle->getNamespace())) {
                $bundleTables[] = $entityMetadata->getTableName();
            }
        }

        $tool = new SchemaTool($this->em);
        $fromSchema = $this->em->getConnection()->getSchemaManager()->createSchema();
        $toSchema = $tool->getSchemaFromMetadata($metadata);
        $filterSchema = function (Schema $schema, array $bundleTables) {
            foreach ($schema->getTables() as $table) {
                if (!in_array($table->getName(), $bundleTables)) {
                    $schema->dropTable($table->getName());
                }
            }
        };
        $filterSchema($fromSchema, $bundleTables);
        $filterSchema($toSchema, $bundleTables);

        $platform = new \Doctrine\DBAL\Platforms\MySqlPlatform();

        $upQueries = $fromSchema->getMigrateToSql($toSchema, $platform);
        $downQueries = $fromSchema->getMigrateFromSql($toSchema, $platform);

        return array(
            self::QUERIES_UP => $upQueries,
            self::QUERIES_DOWN => $downQueries
        );
    }
}