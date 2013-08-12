<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\MigrationBundle\Migrator\Migrator as BaseMigrator;
use Doctrine\DBAL\Migrations\MigrationException;

class MigratorTest extends MockeryTestCase
{
    private $migrator;
    private $baseMigrator;
    private $plugin;

    protected function setUp()
    {
        parent::setUp();
        $this->baseMigrator = $this->mock('Claroline\MigrationBundle\Migrator\Migrator');
        $this->plugin = $this->mock('Claroline\CoreBundle\Library\PluginBundle');
        $this->migrator = new Migrator($this->baseMigrator);
    }

    /**
     * @dataProvider directionProvider
     */
    public function testInstallAndUninstall($method, $direction)
    {
        $this->baseMigrator->shouldReceive('migrate')
            ->once()
            ->with($this->plugin, BaseMigrator::VERSION_FARTHEST, $direction);
        $this->migrator->{$method}($this->plugin);
    }

    /**
     * @dataProvider directionProvider
     * @expectedException RuntimeException
     */
    public function testInstallAndUninstallRethrowExceptionIfNotANoMigrationException($method, $direction)
    {
        $noMigrationException = MigrationException::noMigrationsToExecute();
        $this->baseMigrator->shouldReceive('migrate')
            ->once()
            ->with($this->plugin, BaseMigrator::VERSION_FARTHEST, $direction)
            ->andThrow($noMigrationException); // should not be re-thrown
        $this->migrator->{$method}($this->plugin);

        $this->baseMigrator->shouldReceive('migrate')
            ->once()
            ->with($this->plugin, BaseMigrator::VERSION_FARTHEST, $direction)
            ->andThrow('RuntimeException'); // should be re-thrown
        $this->migrator->{$method}($this->plugin);
    }

    /**
     * @dataProvider versionProvider
     */
    public function testMigrate($targetVersion, $expectedDirection)
    {
        $this->baseMigrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($this->plugin)
            ->andReturn('123');
        $this->baseMigrator->shouldReceive('migrate')
            ->once()
            ->with($this->plugin, $targetVersion, $expectedDirection);
        $this->migrator->migrate($this->plugin, $targetVersion);
    }

    public function testMigrateDoesNothingIfTargetVersionIfTheSameThanCurrent()
    {
        $this->baseMigrator->shouldReceive('getCurrentVersion')
            ->once()
            ->with($this->plugin)
            ->andReturn('123');
        $this->baseMigrator->shouldReceive('migrate')->never();
        $this->migrator->migrate($this->plugin, '123');
    }

    public function directionProvider()
    {
        return array(
            array('install', BaseMigrator::DIRECTION_UP),
            array('remove', BaseMigrator::DIRECTION_DOWN)
        );
    }

    public function versionProvider()
    {
        return array(
            array('456', BaseMigrator::DIRECTION_UP),
            array('100', BaseMigrator::DIRECTION_DOWN)
        );
    }
}
