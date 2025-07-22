<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Tests\Migrations;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\InstallationBundle\Migrations\Generator;
use Doctrine\DBAL\Schema\Comparator;
use Mockery as m;
use Mockery\MockInterface;

class GeneratorTest extends MockeryTestCase
{
    private MockInterface $em;
    private MockInterface $schemaTool;
    private MockInterface $fromSchema;
    private MockInterface $toSchema;
    private MockInterface $entityAMetadata;
    private MockInterface $entityBMetadata;
    private array $metadata;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = m::mock('Doctrine\ORM\EntityManager');
        $this->schemaTool = m::mock('Doctrine\ORM\Tools\SchemaTool');
        $this->fromSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->toSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->entityAMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadata');
        $this->entityBMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadata');
        $this->metadata = [$this->entityAMetadata, $this->entityBMetadata];
    }

    public function testGenerateMigrationQueries(): void
    {
        $schemas = [
            'metadata' => $this->metadata,
            'toSchema' => $this->toSchema,
            'fromSchema' => $this->fromSchema,
        ];
        $generator = m::mock(
            'Claroline\InstallationBundle\Migrations\Generator[getSchemas]',
            [$this->em, $this->schemaTool]
        );
        $generator->shouldReceive('getSchemas')->once()->andReturn($schemas);

        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $schemaManager = m::mock('Doctrine\DBAL\Schema\AbstractSchemaManager');
        $connection = m::mock('Doctrine\DBAL\Connection');
        $connection->shouldReceive('createSchemaManager')->once()->andReturn($schemaManager);
        $schemaManager->shouldReceive('createComparator')->once()->andReturn(new Comparator($platform));
        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        // data set up
        $tableA = m::mock('Doctrine\DBAL\Schema\Table');
        $tableB = m::mock('Doctrine\DBAL\Schema\Table');
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');

        $tableA->shouldReceive('getName')->andReturn('table_a');
        $tableB->shouldReceive('getName')->andReturn('table_b');
        $bundle->shouldReceive('getNamespace')->andReturn('Bar');
        $this->entityAMetadata->name = 'Foo\EntityA';
        $this->entityBMetadata->name = 'Bar\EntityB'; // belongs to the target bundle by its namespace
        $this->entityAMetadata->shouldReceive('getTableName')->andReturn('table_a');
        $this->entityBMetadata->shouldReceive('getTableName')->andReturn('table_b');

        $this->fromSchema->shouldReceive('getTables')->once()->andReturn([$tableA]);
        $this->fromSchema->shouldReceive('getNamespaces')->andReturn(['Bar']);
        $this->fromSchema->shouldReceive('hasNamespace')->with('Bar')->andReturn(true);

        $this->toSchema->shouldReceive('getTables')->once()->andReturn([$tableA, $tableB]);
        $this->toSchema->shouldReceive('getNamespaces')->andReturn(['Bar']);
        $this->toSchema->shouldReceive('hasNamespace')->with('Bar')->andReturn(true);

        // only tables belonging to the target bundle must be kept
        $this->fromSchema->shouldReceive('dropTable')->once()->with('table_a');
        $this->toSchema->shouldReceive('dropTable')->once()->with('table_a');

        $platform->shouldReceive('getAlterSchemaSQL')
            ->once()
            ->with($this->toSchema, $platform)
            ->andReturn(['CREATE TABLE table_b']);
        $platform->shouldReceive('getAlterSchemaSQL')
            ->once()
            ->with($this->toSchema, $platform)
            ->andReturn(['DROP TABLE table_b']);

        static::assertEquals(
            [
                Generator::QUERIES_UP => ['CREATE TABLE table_b'],
                Generator::QUERIES_DOWN => ['DROP TABLE table_b'],
            ],
            $generator->generateMigrationQueries($bundle, $platform)
        );
    }

    public function testGetSchemas(): void
    {
        $generator = new Generator($this->em, $this->schemaTool);
        $metadataFactory = m::mock('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $connection = m::mock('Doctrine\DBAL\Connection');
        $schemaManager = m::mock('Doctrine\DBAL\Schema\AbstractSchemaManager');

        $this->em->shouldReceive('getMetadataFactory')->once()->andReturn($metadataFactory);
        $metadataFactory->shouldReceive('getAllMetadata')->once()->andReturn($this->metadata);
        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);
        $connection->shouldReceive('createSchemaManager')->once()->andReturn($schemaManager);
        $schemaManager->shouldReceive('introspectSchema')->once()->andReturn($this->fromSchema);
        $this->schemaTool->shouldReceive('getSchemaFromMetadata')
            ->once()
            ->with($this->metadata)
            ->andReturn($this->toSchema);

        // multiple calls should return different clones of the same schemas
        $schemasA = $generator->getSchemas();
        $schemasB = $generator->getSchemas();

        static::assertCount(3, $schemasA);
        static::assertCount(3, $schemasB);
        static::assertEquals($schemasA['metadata'], $this->metadata);
        static::assertEquals($schemasB['metadata'], $this->metadata);

        static::assertEquals($schemasA['fromSchema'], $this->fromSchema);
        static::assertNotSame($schemasA['fromSchema'], $this->fromSchema);
        static::assertEquals($schemasA['toSchema'], $this->toSchema);
        static::assertNotSame($schemasA['toSchema'], $this->toSchema);

        static::assertEquals($schemasB['fromSchema'], $this->fromSchema);
        static::assertEquals($schemasB['toSchema'], $this->toSchema);
        static::assertNotSame($schemasB['fromSchema'], $this->fromSchema);
        static::assertNotSame($schemasB['toSchema'], $this->toSchema);

        static::assertNotSame($schemasA['fromSchema'], $schemasB['fromSchema']);
        static::assertNotSame($schemasA['toSchema'], $schemasB['toSchema']);
    }
}
