<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.install.bundle_migrator", public=false)
 */
class BundleMigrator
{
    protected $migrationBuilder;

    /**
     * @DI\InjectParams({
     *     "builder" = @Di\Inject("claroline.install.migration_builder")
     * })
     */
    public function __construct(MigrationBuilder $builder)
    {
        $this->migrationBuilder = $builder;
    }

    public function createSchemaForBundle(Bundle $bundle)
    {
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);

        foreach ($migrations as $migration) {
            $migration->migrate(null);
        }
    }

    public function dropSchemaForBundle(Bundle $bundle)
    {
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);

        foreach ($migrations as $migration) {
            $migration->migrate('0');
        }
    }

    public function migrateBundle(Bundle $bundle, $version = null)
    {
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);

        foreach ($migrations as $migration) {
            $migration->migrate($version);
        }
    }
}