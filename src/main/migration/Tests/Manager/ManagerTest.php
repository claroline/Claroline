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

use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\MigrationBundle\Tests\MockeryTestCase;
use Mockery as m;

class ManagerTest extends MockeryTestCase
{
    private $generator;
    private $writer;
    private $migrator;
    private $manager;

    protected function setUp(): void
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
            [$this->generator, $this->writer, $this->migrator]
        );
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $bundleConfig = m::mock('Claroline\MigrationBundle\Migrator\BundleMigration');

        $this->migrator->shouldReceive('getConfiguration')
            ->once()
            ->with($bundle)
            ->andReturn($bundleConfig);

        $bundleConfig->shouldReceive('generateClassName')
            ->once()
            ->andReturn('VersionTest');

        $manager->shouldReceive('getAvailablePlatforms')
            ->once()
            ->andReturn(['driver' => $platform]);
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

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        $this->assertTrue(true);
    }

    public function testGetAvailablePlatforms()
    {
        $platforms = $this->manager->getAvailablePlatforms();
        $this->assertContains('pdo_mysql', array_keys($platforms));
        $this->assertInstanceOf('Doctrine\DBAL\Platforms\AbstractPlatform', $platforms['pdo_mysql']);
    }

    public function testGetBundleStatus()
    {
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');

        $this->migrator->shouldReceive('getMigrationStatus')
            ->once()
            ->with($bundle)
            ->andReturn([
                'current' => null,
                'latest' => null,
                'available' => [],
            ]);

        $bundleStatus = $this->manager->getBundleStatus($bundle);

        $this->assertIsArray($bundleStatus);
        $this->assertArrayHasKey('current', $bundleStatus);
        $this->assertArrayHasKey('latest', $bundleStatus);
        $this->assertArrayHasKey('available', $bundleStatus);
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

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        $this->assertTrue(true);
    }

    public function testDiscardUpperMigrations()
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager[getAvailablePlatforms]',
            [$this->generator, $this->writer, $this->migrator]
        );
        $manager->shouldReceive('getAvailablePlatforms')
            ->once()
            ->andReturn(['driver1' => 'd1', 'driver2' => 'd2']);
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

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        $this->assertTrue(true);
    }

    public function queriesProvider()
    {
        return [
            [
                [
                    Generator::QUERIES_UP => ['up queries'],
                    Generator::QUERIES_DOWN => ['down queries'],
                ],
                false,
            ],
            [
                [
                    Generator::QUERIES_UP => [],
                    Generator::QUERIES_DOWN => [],
                ],
                true,
            ],
        ];
    }

    public function migrationProvider()
    {
        return [
            [Migrator::DIRECTION_UP, 'upgradeBundle'],
            [Migrator::DIRECTION_DOWN, 'downgradeBundle'],
        ];
    }
}
