<?php

namespace Claroline\MigrationBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Generator
{
    const QUERIES_UP = 'up';
    const QUERIES_DOWN = 'down';

    private $em;
    private $schemaTool;

    public function __construct(EntityManager $em, SchemaTool $tool)
    {
        $this->em = $em;
        $this->schemaTool = $tool;
    }

    /**
     * Generates bundle migration queries (up and down) for a given SQL platform.
     *
     * @param Symfony\Component\HttpKernel\Bundle\Bundle    $bundle
     * @param Doctrine\DBAL\Platforms\AbstractPlatform      $platform
     *
     * @return array
     *
     * @todo keep generated schemas in cache for susbsequent calls
     */
    public function generateMigrationQueries(Bundle $bundle, AbstractPlatform $platform)
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $fromSchema = $this->em->getConnection()->getSchemaManager()->createSchema();
        $toSchema = $this->schemaTool->getSchemaFromMetadata($metadata);

        $bundleTables = $this->getBundleTables($bundle, $metadata);
        $this->filterSchemas(array($fromSchema, $toSchema), $bundleTables);

        $upQueries = $fromSchema->getMigrateToSql($toSchema, $platform);
        $downQueries = $fromSchema->getMigrateFromSql($toSchema, $platform);

        return array(
            self::QUERIES_UP => $upQueries,
            self::QUERIES_DOWN => $downQueries
        );
    }

    private function getBundleTables(Bundle $bundle, array $metadata)
    {
        $bundleTables = array();

        foreach ($metadata as $entityMetadata) {
            if (0 === strpos($entityMetadata->name, $bundle->getNamespace())) {
                $bundleTables[] = $entityMetadata->getTableName();
            }
        }

        return $bundleTables;
    }

    private function filterSchemas(array $schemas, array $bundleTables)
    {
        foreach ($schemas as $schema) {
            foreach ($schema->getTables() as $table) {
                if (!in_array($table->getName(), $bundleTables)) {
                    $schema->dropTable($table->getName());
                }
            }
        }
    }
}
