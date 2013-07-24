<?php

namespace Claroline\MigrationBundle\Generator;

use Mockery as m;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $em;
    private $metadataFactory;
    private $connection;
    private $schemaManager;
    private $schemaTool;
    private $bundle;
    private $fromSchema;
    private $toSchema;
    private $entityAMetadata;
    private $entityBMetadata;
    private $tableA;
    private $tableB;
    private $platform;
    private $generator;

    protected function setUp()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        m::getConfiguration()->allowMockingMethodsUnnecessarily(false);

        $this->em = m::mock('Doctrine\ORM\EntityManager');
        $this->metadataFactory = m::mock('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $this->connection = m::mock('Doctrine\DBAL\Connection');
        $this->schemaManager = m::mock('Doctrine\DBAL\Schema\AbstractSchemaManager');
        $this->schemaTool = m::mock('Doctrine\ORM\Tools\SchemaTool');
        $this->bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->fromSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->toSchema = m::mock('Doctrine\DBAL\Schema\Schema');
        $this->entityAMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadataInfo');
        $this->entityBMetadata = m::mock('Doctrine\ORM\Mapping\ClassMetadataInfo');
        $this->tableA = m::mock('Doctrine\DBAL\Schema\Table');
        $this->tableB = m::mock('Doctrine\DBAL\Schema\Table');
        $this->platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $this->generator = new Generator($this->em, $this->schemaTool);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testGenerateMigrationQueries()
    {
        // data set up
        $metadata = array($this->entityAMetadata, $this->entityBMetadata);
        $this->entityAMetadata->name = 'Foo\EntityA';
        $this->entityBMetadata->name = 'Bar\EntityB';
        $this->entityAMetadata->shouldReceive('getTableName')->andReturn('table_a');
        $this->entityBMetadata->shouldReceive('getTableName')->andReturn('table_b');
        $this->tableA->shouldReceive('getName')->andReturn('table_a');
        $this->tableB->shouldReceive('getName')->andReturn('table_b');
        $this->bundle->shouldReceive('getNamespace')->andReturn('Bar');

        // schemas creation
        $this->em->shouldReceive('getMetadataFactory')->once()->andReturn($this->metadataFactory);
        $this->metadataFactory->shouldReceive('getAllMetadata')->once()->andReturn($metadata);
        $this->em->shouldReceive('getConnection')->once()->andReturn($this->connection);
        $this->connection->shouldReceive('getSchemaManager')->once()->andReturn($this->schemaManager);
        $this->schemaManager->shouldReceive('createSchema')->once()->andReturn($this->fromSchema);
        $this->schemaTool->shouldReceive('getSchemaFromMetadata')->once()->with($metadata)->andReturn($this->toSchema);

        $this->fromSchema->shouldReceive('getTables')->once()->andReturn(array($this->tableA));
        $this->toSchema->shouldReceive('getTables')->once()->andReturn(array($this->tableA, $this->tableB));

        // only tables belonging to the target bundle must be kept
        $this->fromSchema->shouldReceive('dropTable')->once()->with('table_a');
        $this->toSchema->shouldReceive('dropTable')->once()->with('table_a');

        $this->fromSchema->shouldReceive('getMigrateToSql')
            ->once()
            ->with($this->toSchema, $this->platform)
            ->andReturn(array('CREATE TABLE table_b'));
        $this->fromSchema->shouldReceive('getMigrateFromSql')
            ->once()
            ->with($this->toSchema, $this->platform)
            ->andReturn(array('DROP TABLE table_b'));

        $this->assertEquals(
            array(
                Generator::QUERIES_UP => array('CREATE TABLE table_b'),
                Generator::QUERIES_DOWN => array('DROP TABLE table_b')
            ),
            $this->generator->generateMigrationQueries($this->bundle, $this->platform)
        );
    }
}