<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Tests\Manager;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Mockery as m;

class ManagerTest extends MockeryTestCase
{
    private $connection;
    private $generator;
    private $writer;
    private $migrator;
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = m::mock('Doctrine\DBAL\Connection');
        $this->writer = m::mock('Claroline\MigrationBundle\Generator\Writer');
        $this->writer = m::mock('Claroline\MigrationBundle\Generator\Writer');
        $this->generator = m::mock('Claroline\MigrationBundle\Generator\Generator');
        $this->migrator = m::mock('Claroline\MigrationBundle\Migrator\Migrator');
        $this->manager = new Manager($this->connection, $this->generator, $this->writer, $this->migrator);
    }

    /**
     * @dataProvider queriesProvider
     */
    public function testGenerateBundleMigration(array $queries, $areQueriesEmpty): void
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager',
            [$this->connection, $this->generator, $this->writer, $this->migrator]
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

        $this->generator->shouldReceive('generateMigrationQueries')
            ->once()
            ->with($bundle, $platform)
            ->andReturn($queries);

        if (!$areQueriesEmpty) {
            $this->writer->shouldReceive('writeMigrationClass')
                ->once()
                ->with($bundle, m::any(), $queries);
        }

        $manager->generateBundleMigration($bundle);

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        $this->assertTrue(true);
    }

    public function testGetBundleStatus(): void
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
    public function testMigrate($direction, $method): void
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

    public function testDiscardUpperMigrations(): void
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Manager\Manager',
            [$this->generator, $this->writer, $this->migrator]
        );

        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->migrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($bundle)
            ->andReturn('1');
        $this->writer->shouldReceive('deleteUpperMigrationClasses')
            ->once()
            ->with($bundle, '1');

        $manager->discardUpperMigrations($bundle);

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        $this->assertTrue(true);
    }

    public function queriesProvider(): array
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

    public function migrationProvider(): array
    {
        return [
            [Migrator::DIRECTION_UP, 'upgradeBundle'],
            [Migrator::DIRECTION_DOWN, 'downgradeBundle'],
        ];
    }
}
