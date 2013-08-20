<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Doctrine\DBAL\Migrations\MigrationException;
use Claroline\MigrationBundle\Migrator\Migrator as BaseMigrator;
use Claroline\CoreBundle\Library\PluginBundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * The migrator is used to create/update/drop the plugin database tables.
 *
 * @DI\Service("claroline.plugin.migrator")
 */
class Migrator
{
    private $migrator;

    /**
     * Constructor.
     *
     * @param BundleMigrator $migrator
     *
     * @DI\InjectParams({
     *     "migrator" = @DI\Inject("claroline.migration.migrator")
     * })
     */
    public function __construct(BaseMigrator $migrator)
    {
        $this->migrator = $migrator;
    }

    /**
     * Creates the tables of a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function install(PluginBundle $plugin)
    {
        $this->doMigrate($plugin, BaseMigrator::DIRECTION_UP);
    }

    /**
     * Drops the tables of a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function remove(PluginBundle $plugin)
    {
        $this->doMigrate($plugin, BaseMigrator::DIRECTION_DOWN);
    }

    public function doMigrate(PluginBundle $plugin, $direction)
    {
        try {
            $this->migrator->migrate($plugin, BaseMigrator::VERSION_FARTHEST, $direction);
        } catch (MigrationException $ex) {
            // code 4 == no migration to execute (harmless)
            if ($ex->getCode() !== 4) {
                throw $ex;
            }
        }
    }
}
