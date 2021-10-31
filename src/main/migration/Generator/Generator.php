<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class responsible for generating bundle migration queries.
 */
class Generator
{
    const QUERIES_UP = 'up';
    const QUERIES_DOWN = 'down';

    private $em;
    private $schemaTool;
    private $schemas = [];

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em, SchemaTool $tool)
    {
        $this->em = $em;
        $this->schemaTool = $tool;
    }

    /**
     * Generates bundle migration queries (up and down) for a given SQL platform.
     *
     * @return array
     */
    public function generateMigrationQueries(BundleInterface $bundle, AbstractPlatform $platform)
    {
        $schemas = $this->getSchemas();
        $fromSchema = $schemas['fromSchema'];
        $toSchema = $schemas['toSchema'];

        $bundleTables = $this->getBundleTables($bundle, $schemas['metadata']);
        $this->filterSchemas([$fromSchema, $toSchema], $bundleTables);

        $upQueries = $fromSchema->getMigrateToSql($toSchema, $platform);
        $downQueries = $fromSchema->getMigrateFromSql($toSchema, $platform);

        return [
            self::QUERIES_UP => $upQueries,
            self::QUERIES_DOWN => $downQueries,
        ];
    }

    /**
     * Returns the "from" an "to" schemas and the metadata used to generate them.
     *
     * Note: this method is public for testing purposes only
     *
     * @return array
     */
    public function getSchemas()
    {
        if (0 === count($this->schemas)) {
            $this->schemas['metadata'] = $this->em->getMetadataFactory()->getAllMetadata();
            $this->schemas['fromSchema'] = $this->em->getConnection()->getSchemaManager()->createSchema();
            $this->schemas['toSchema'] = $this->schemaTool->getSchemaFromMetadata($this->schemas['metadata']);
        }

        // cloning schemas is much more ligther than re-generating them for each platform
        return [
            'fromSchema' => clone $this->schemas['fromSchema'],
            'toSchema' => clone $this->schemas['toSchema'],
            'metadata' => $this->schemas['metadata'],
        ];
    }

    private function getBundleTables(BundleInterface $bundle, array $metadata)
    {
        $bundleTables = ['tables' => [], 'joinTables' => []];

        foreach ($metadata as $entityMetadata) {
            if (0 === strpos($entityMetadata->name, $bundle->getNamespace())) {
                $bundleTables[] = $entityMetadata->getTableName();

                foreach ($entityMetadata->associationMappings as $association) {
                    if (isset($association['joinTable']['name'])) {
                        $bundleTables[] = $association['joinTable']['name'];
                    }
                }
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
