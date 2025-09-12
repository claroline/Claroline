<?php

namespace Claroline\InstallationBundle\Tests\Manager;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\InstallationBundle\Manager\MigrationsManager;
use Claroline\InstallationBundle\Migrations\Generator;
use Claroline\InstallationBundle\Migrations\Migrator;
use Mockery as m;
use Mockery\MockInterface;

class MigrationsManagerTest extends MockeryTestCase
{
    private MockInterface $connection;
    private MockInterface $generator;
    private MockInterface $writer;
    private MockInterface $migrator;
    private MigrationsManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = m::mock('Doctrine\DBAL\Connection');
        $this->writer = m::mock('Claroline\InstallationBundle\Migrations\Writer');
        $this->writer = m::mock('Claroline\InstallationBundle\Migrations\Writer');
        $this->generator = m::mock('Claroline\InstallationBundle\Migrations\Generator');
        $this->migrator = m::mock('Claroline\InstallationBundle\Migrations\Migrator');
        $this->manager = new MigrationsManager($this->connection, $this->generator, $this->writer, $this->migrator);
    }

    /**
     * @dataProvider queriesProvider
     */
    public function testGenerateBundleMigration(array $queries, $areQueriesEmpty): void
    {
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $bundleConfig = m::mock('Claroline\InstallationBundle\Migrations\BundleMigration');

        $this->connection->shouldReceive('getDatabasePlatform')
            ->once()
            ->andReturn($platform);

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

        $this->manager->generateBundleMigration($bundle);

        // to remove the "risky" warning
        // if the method call fails, the assertion will not be checked and the test will fail
        static::assertTrue(true);
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

        static::assertIsArray($bundleStatus);
        static::assertArrayHasKey('current', $bundleStatus);
        static::assertArrayHasKey('latest', $bundleStatus);
        static::assertArrayHasKey('available', $bundleStatus);
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
        static::assertTrue(true);
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
