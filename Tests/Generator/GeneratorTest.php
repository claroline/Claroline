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

use Mockery as m;
use Claroline\MigrationBundle\Tests\MockeryTestCase;

class GeneratorTest extends MockeryTestCase
{
    private $em;
    private $schemaTool;
    private $fromSchema;
    private $toSchema;
    private $metadata;
    private $entityAMetadata;
    private $entityBMetadata;

    protected function setUp()
    {
        parent::setUp();
        $this->em = m::mock('Doctrine\ORM\EntityManager');
        $this->schemaTool = m::mock('Doctrine\ORM\Tools\SchemaTool');
        $this->fromSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->toSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->entityAMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadataInfo');
        $this->entityBMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadataInfo');
        $this->metadata = array($this->entityAMetadata, $this->entityBMetadata);
    }

    public function testGenerateMigrationQueries()
    {
        $schemas = array(
            'metadata' => $this->metadata,
            'toSchema' => $this->toSchema,
            'fromSchema' => $this->fromSchema
        );
        $generator = m::mock(
            'Claroline\MigrationBundle\Generator\Generator[getSchemas]',
            array($this->em, $this->schemaTool)
        );
        $generator->shouldReceive('getSchemas')->once()->andReturn($schemas);

        // data set up
        $tableA = m::mock('Doctrine\DBAL\Schema\Table');
        $tableB = m::mock('Doctrine\DBAL\Schema\Table');
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $tableA->shouldReceive('getName')->andReturn('table_a');
        $tableB->shouldReceive('getName')->andReturn('table_b');
        $bundle->shouldReceive('getNamespace')->andReturn('Bar');
        $this->entityAMetadata->name = 'Foo\EntityA';
        $this->entityBMetadata->name = 'Bar\EntityB'; // belongs to the target bundle by its namespace
        $this->entityAMetadata->shouldReceive('getTableName')->andReturn('table_a');
        $this->entityBMetadata->shouldReceive('getTableName')->andReturn('table_b');

        $this->fromSchema->shouldReceive('getTables')->once()->andReturn(array($tableA));
        $this->toSchema->shouldReceive('getTables')->once()->andReturn(array($tableA, $tableB));

        // only tables belonging to the target bundle must be kept
        $this->fromSchema->shouldReceive('dropTable')->once()->with('table_a');
        $this->toSchema->shouldReceive('dropTable')->once()->with('table_a');

        $this->fromSchema->shouldReceive('getMigrateToSql')
            ->once()
            ->with($this->toSchema, $platform)
            ->andReturn(array('CREATE TABLE table_b'));
        $this->fromSchema->shouldReceive('getMigrateFromSql')
            ->once()
            ->with($this->toSchema, $platform)
            ->andReturn(array('DROP TABLE table_b'));

        $this->assertEquals(
            array(
                Generator::QUERIES_UP => array('CREATE TABLE table_b'),
                Generator::QUERIES_DOWN => array('DROP TABLE table_b')
            ),
            $generator->generateMigrationQueries($bundle, $platform)
        );
    }

    public function testGetSchemas()
    {
        $generator = new Generator($this->em, $this->schemaTool);
        $metadataFactory = m::mock('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $connection = m::mock('Doctrine\DBAL\Connection');
        $schemaManager = m::mock('Doctrine\DBAL\Schema\AbstractSchemaManager');

        $this->em->shouldReceive('getMetadataFactory')->once()->andReturn($metadataFactory);
        $metadataFactory->shouldReceive('getAllMetadata')->once()->andReturn($this->metadata);
        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);
        $connection->shouldReceive('getSchemaManager')->once()->andReturn($schemaManager);
        $schemaManager->shouldReceive('createSchema')->once()->andReturn($this->fromSchema);
        $this->schemaTool->shouldReceive('getSchemaFromMetadata')
            ->once()
            ->with($this->metadata)
            ->andReturn($this->toSchema);

        // multiple calls should return different clones of the same schemas
        $schemasA = $generator->getSchemas();
        $schemasB = $generator->getSchemas();

        $this->assertEquals(3, count($schemasA));
        $this->assertEquals(3, count($schemasB));
        $this->assertEquals($schemasA['metadata'], $this->metadata);
        $this->assertEquals($schemasB['metadata'], $this->metadata);

        $this->assertEquals($schemasA['fromSchema'], $this->fromSchema);
        $this->assertNotSame($schemasA['fromSchema'], $this->fromSchema);
        $this->assertEquals($schemasA['toSchema'], $this->toSchema);
        $this->assertNotSame($schemasA['toSchema'], $this->toSchema);

        $this->assertEquals($schemasB['fromSchema'], $this->fromSchema);
        $this->assertEquals($schemasB['toSchema'], $this->toSchema);
        $this->assertNotSame($schemasB['fromSchema'], $this->fromSchema);
        $this->assertNotSame($schemasB['toSchema'], $this->toSchema);

        $this->assertNotSame($schemasA['fromSchema'], $schemasB['fromSchema']);
        $this->assertNotSame($schemasA['toSchema'], $schemasB['toSchema']);
    }
}
