<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Manager;

use Mockery as m;
use Claroline\MigrationBundle\Tests\MockeryTestCase;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Migrator\Migrator;

class ManagerTest extends MockeryTestCase
{
    private $generator;
    private $writer;
    private $migrator;
    private $manager;

    protected function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\MigrationBundle\Generator\Writer');
        $this->generator = m::mock('Claroline\MigrationBundle\Generator\Generator');
        $this->migrator = m::mock('Claroline\MigrationBundle\Migrator\Migrator');
        $this->manager = new Manager($this->generator, $this->writer, $this->migrator);
    }

    /**
     * @dataProvider queriesProvider
     */
    public function testGenerateBundleMigration(array $queries, $areQueriesEmpty)
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager[getAvailablePlatforms]',
            array($this->generator, $this->writer, $this->migrator)
        );
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');

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

        $manager->generateBundleMigration($bundle);
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

        $this->migrator->shouldReceive('getMigrationStatus')
            ->once()
            ->with($bundle)
            ->andReturn('status');
        $this->assertEquals('status', $this->manager->getBundleStatus($bundle));
    }

    /**
     * @dataProvider migrationProvider
     */
    public function testMigrate($direction, $method)
    {
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->migrator->shouldReceive('migrate')
            ->once()
            ->with($bundle, '123', $direction);
        $this->migrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($bundle);
        $this->manager->{$method}($bundle, '123');
    }

    public function testDiscardUpperMigrations()
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager[getAvailablePlatforms]',
            array($this->generator, $this->writer, $this->migrator)
        );
        $manager->shouldReceive('getAvailablePlatforms')
            ->once()
            ->andReturn(array('driver1' => 'd1', 'driver2' => 'd2'));
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->migrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($bundle)
            ->andReturn('1');
        $this->writer->shouldReceive('deleteUpperMigrationClasses')
            ->once()
            ->with($bundle, 'driver1', '1');
        $this->writer->shouldReceive('deleteUpperMigrationClasses')
            ->once()
            ->with($bundle, 'driver2', '1');
        $manager->discardUpperMigrations($bundle);
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
