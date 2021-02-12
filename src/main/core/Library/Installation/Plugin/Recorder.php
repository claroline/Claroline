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

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Psr\Log\LoggerInterface;

/**
 * This recorder is used to register a plugin both in database and in the
 * application configuration files. It uses dedicated components to perform
 * this task.
 *
 * @todo Remove this class (as there's only one writer now -> refactor DatabaseWriter)
 */
class Recorder
{
    /** @var DatabaseWriter */
    private $dbWriter;
    /** @var Validator */
    private $validator;

    public function __construct(
        DatabaseWriter $dbWriter,
        Validator $validator)
    {
        $this->dbWriter = $dbWriter;
        $this->validator = $validator;
    }

    /**
     * Sets the database writer.
     */
    public function setDatabaseWriter(DatabaseWriter $writer)
    {
        $this->dbWriter = $writer;
    }

    /**
     * Registers a plugin.
     *
     * @return Plugin
     */
    public function register(PluginBundleInterface $plugin)
    {
        $this->validate($plugin, false);

        return $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
    }

    /**
     * Update configuration for a plugin.
     */
    public function update(PluginBundleInterface $plugin)
    {
        $this->validate($plugin, true);

        $this->dbWriter->update($plugin, $this->validator->getPluginConfiguration());
    }

    /**
     * Unregisters a plugin.
     */
    public function unregister(PluginBundleInterface $plugin)
    {
        $pluginFqcn = get_class($plugin);
        $this->dbWriter->delete($pluginFqcn);
    }

    /**
     * Checks if a plugin is registered.
     *
     * @return bool
     */
    public function isRegistered(PluginBundleInterface $plugin)
    {
        return $this->dbWriter->isSaved($plugin);
    }

    public function validate(PluginBundleInterface $plugin, $update = false)
    {
        if ($update) {
            $this->validator->activeUpdateMode();
        }
        $errors = $this->validator->validate($plugin);
        $this->validator->deactivateUpdateMode();

        if (!empty($errors)) {
            $report = "Plugin '{$plugin->getNamespace()}' cannot be installed, due to the "
                .'following validation errors :'.PHP_EOL;

            foreach ($errors as $error) {
                $report .= $error->getMessage().PHP_EOL;
            }

            throw new \Exception($report);
        }
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->dbWriter->setLogger($logger);
    }
}
