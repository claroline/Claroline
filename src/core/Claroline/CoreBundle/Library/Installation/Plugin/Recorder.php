<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * This recorder is used to register a plugin both in database and in the
 * application configuration files. It uses dedicated components to perform
 * this task.
 */
class Recorder
{
    private $configWriter;
    private $dbWriter;

    /**
     * Constructor.
     *
     * @param ConfigurationFileWriter   $configWriter
     * @param DatabaseWriter            $dbWriter
     */
    public function __construct(ConfigurationFileWriter $configWriter, DatabaseWriter $dbWriter)
    {
        $this->configWriter = $configWriter;
        $this->dbWriter = $dbWriter;
    }

    /**
     * Sets the configuration file writer.
     *
     * @param ConfigurationFileWriter $writer
     */
    public function setConfigurationFileWriter(ConfigurationFileWriter $writer)
    {
        $this->configWriter = $writer;
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
    public function register(PluginBundle $plugin)
    {
        $pluginFqcn = get_class($plugin);

        $this->dbWriter->insert($plugin);
        $this->configWriter->registerNamespace($plugin->getVendorName());
        $this->configWriter->addInstantiableBundle($pluginFqcn);
        $this->configWriter->importRoutingResources(
            $pluginFqcn, $plugin->getRoutingResourcesPaths(), $plugin->getRoutingPrefix()
        );
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
        $this->configWriter->removeNamespace($plugin->getVendorName());
        $this->configWriter->removeInstantiableBundle($pluginFqcn);
        $this->configWriter->removeRoutingResources($pluginFqcn);
    }

    /**
     * Checks if a plugin is registered.
     *
     * @param string $pluginFqcn
     *
     * @return boolean
     */
    public function isRegistered($pluginFqcn)
    {
        $isSavedInDb = $this->dbWriter->isSaved($pluginFqcn);
        $isSavedInConfig = $this->configWriter->isRecorded($pluginFqcn);

        if ($isSavedInDb && $isSavedInConfig) {
            return true;
        }

        return false;
    }
}