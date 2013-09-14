<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This recorder is used to register a plugin both in database and in the
 * application configuration files. It uses dedicated components to perform
 * this task.
 *
 * @DI\Service("claroline.plugin.recorder")
 */
class Recorder
{
    private $dbWriter;

    /**
     * Constructor.
     *
     * @param DatabaseWriter dbWriter
     *
     * @DI\InjectParams({
     *     "dbWriter" = @DI\Inject("claroline.plugin.recorder_database_writer")
     * })
     */
    public function __construct(DatabaseWriter $dbWriter)
    {
        $this->dbWriter = $dbWriter;
    }

    /**
     * Sets the database writer.
     *
     * @param DatabaseWriter $writer
     */
    public function setDatabaseWriter(DatabaseWriter $writer)
    {
        $this->dbWriter = $writer;
    }

    /**
     * Registers a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function register(PluginBundle $plugin, array $pluginConfiguration)
    {
        $pluginFqcn = get_class($plugin);
        $this->dbWriter->insert($plugin, $pluginConfiguration);
    }

    /**
     * Unregisters a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function unregister(PluginBundle $plugin)
    {
        $pluginFqcn = get_class($plugin);
        $this->dbWriter->delete($pluginFqcn);
    }

    /**
     * Checks if a plugin is registered.
     *
     * @param string $pluginFqcn
     *
     * @return boolean
     */
    public function isRegistered(PluginBundle $plugin)
    {
        return $this->dbWriter->isSaved($plugin);
    }
}
