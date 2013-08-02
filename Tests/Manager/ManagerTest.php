<?php

namespace Claroline\MigrationBundle\Manager;

use Mockery as m;
use Claroline\MigrationBundle\Tests\MockeryTestCase;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Migrator\Migrator;

class ManagerTest extends MockeryTestCase
{
    private $kernel;
    private $generator;
    private $writer;
    private $migrator;
    private $manager;

    protected function setUp()
    {
        parent::setUp();
        $this->kernel = m::mock('Symfony\Component\HttpKernel\Kernel');
        $this->writer = m::mock('Claroline\MigrationBundle\Generator\Writer');
        $this->generator = m::mock('Claroline\MigrationBundle\Generator\Generator');
        $this->migrator = m::mock('Claroline\MigrationBundle\Migrator\Migrator');
        $this->manager = new Manager($this->kernel, $this->generator, $this->writer, $this->migrator);
    }

    /**
     * @dataProvider queriesProvider
     */
    public function testGenerateBundleMigration(array $queries, $areQueriesEmpty)
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager[getAvailablePlatforms]',
            array($this->kernel, $this->generator, $this->writer, $this->migrator)
        );
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');

        $this->kernel->shouldReceive('getBundle')
            ->once()
            ->with('FooBundle')
            ->andReturn($bundle);
        $manager->shouldReceive('getAvailablePlatforms')
            ->once()
            ->andReturn(array('driver' => $platform));
        $this->generator->shouldReceive('generateMigrationQueries')
            ->once()
            ->with($bundle, $platform)
            ->andReturn($queries);

        if (!$areQueriesEmpty) {
            $this->writer->shouldReceive('writeMigrationClass')
                ->once()
                ->with($bundle, 'driver', m::any(), $queries);
        }

        $manager->generateBundleMigration('FooBundle');
    }

    public function testGetAvailablePlatforms()
    {
        $platforms = $this->manager->getAvailablePlatforms();
        $this->assertGreaterThan(1, count($platforms));
        $this->assertContains('pdo_mysql', array_keys($platforms));
        $this->assertInstanceOf('Doctrine\DBAL\Platforms\AbstractPlatform', $platforms['pdo_mysql']);
    }

    public function testGetBundleStatus()
    {
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->kernel->shouldReceive('getBundle')
            ->once()
            ->with('FooBundle')
            ->andReturn($bundle);
        $this->migrator->shouldReceive('getMigrationStatus')
            ->once()
            ->with($bundle)
            ->andReturn('status');
        $this->assertEquals('status', $this->manager->getBundleStatus('FooBundle'));
    }

    /**
     * @dataProvider migrationProvider
     */
    public function testMigrate($direction, $method)
    {
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->kernel->shouldReceive('getBundle')
            ->once()
            ->with('FooBundle')
            ->andReturn($bundle);
        $this->migrator->shouldReceive('migrate')
            ->once()
            ->with($bundle, '123', $direction);
        $this->migrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($bundle);
        $this->manager->{$method}('FooBundle', '123');
    }

    public function queriesProvider()
    {
        return array(
            array(
                array(
                    Generator::QUERIES_UP => array('up queries'),
                    Generator::QUERIES_DOWN => array('down queries')
                ),
                false
            ),
            array(
                array(
                    Generator::QUERIES_UP => array(),
                    Generator::QUERIES_DOWN => array()
                ),
                true
            ),
        );
    }

    public function migrationProvider()
    {
        return array(
            array(Migrator::DIRECTION_UP, 'upgradeBundle'),
            array(Migrator::DIRECTION_DOWN, 'downgradeBundle')
        );
    }
}
