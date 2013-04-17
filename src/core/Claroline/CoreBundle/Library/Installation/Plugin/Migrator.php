<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Installation\BundleMigrator;
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
     *     "migrator" = @DI\Inject("claroline.install.bundle_migrator")
     * })
     */
    public function __construct(BundleMigrator $migrator)
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
        $this->migrator->createSchemaForBundle($plugin);
    }

    /**
     * Drops the tables of a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function remove(PluginBundle $plugin)
    {
        $this->migrator->dropSchemaForBundle($plugin);
    }

    /**
     * Updates the tables of a plugin.
     *
     * @param PluginBundle $plugin
     *
     * @param string $version
     */
    public function migrate(PluginBundle $plugin, $version)
    {
        $this->migrator->migrateBundle($plugin, $version);
    }
}