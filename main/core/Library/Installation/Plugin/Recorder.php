<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This recorder is used to register a plugin both in database and in the
 * application configuration files. It uses dedicated components to perform
 * this task.
 *
 * @todo Remove this class (as there's only one writer now -> refactor DatabaseWriter)
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
     * @param DistributionPluginBundle $plugin
     * @param array                    $pluginConfiguration
     */
    public function register(DistributionPluginBundle $plugin, array $pluginConfiguration)
    {
        return $this->dbWriter->insert($plugin, $pluginConfiguration);
    }

    /**
     * Update configuration for a plugin.
     *
     * @param DistributionPluginBundle $plugin
     * @param array                    $pluginConfiguration
     */
    public function update(DistributionPluginBundle $plugin, array $pluginConfiguration)
    {
        $this->dbWriter->update($plugin, $pluginConfiguration);
    }

    /**
     * Unregisters a plugin.
     *
     * @param DistributionPluginBundle $plugin
     */
    public function unregister(DistributionPluginBundle $plugin)
    {
        $pluginFqcn = get_class($plugin);
        $this->dbWriter->delete($pluginFqcn);
    }

    /**
     * Checks if a plugin is registered.
     *
     * @param \Claroline\CoreBundle\Library\DistributionPluginBundle $plugin
     *
     * @return bool
     */
    public function isRegistered(DistributionPluginBundle $plugin)
    {
        return $this->dbWriter->isSaved($plugin);
    }

    public function setLogger($logger)
    {
        $this->dbWriter->setLogger($logger);
    }
}
