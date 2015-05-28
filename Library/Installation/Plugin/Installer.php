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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Manager\InstallationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * This class is used to perform the (un-)installation of a plugin.
 *
 * @DI\Service("claroline.plugin.installer")
 */
class Installer
{
    use LoggableTrait;

    private $validator;
    private $recorder;
    private $baseInstaller;
    private $om;

    /**
     * Constructor.
     *
     * @param Validator           $validator
     * @param Recorder            $recorder
     * @param InstallationManager $installer
     *
     * @DI\InjectParams({
     *     "validator" = @DI\Inject("claroline.plugin.validator"),
     *     "recorder"  = @DI\Inject("claroline.plugin.recorder"),
     *     "installer" = @DI\Inject("claroline.installation.manager"),
     *     "om"        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        Validator $validator,
        Recorder $recorder,
        InstallationManager $installer,
        ObjectManager $om
    )
    {
        $this->validator = $validator;
        $this->recorder = $recorder;
        $this->baseInstaller = $installer;
        $this->om = $om;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->baseInstaller->setLogger($logger);
    }

    /**
     * Installs a plugin.
     *
     * PluginBundle $plugin
     *
     * @param PluginBundle $plugin
     *
     * @throws \Exception if the plugin doesn't pass the validation
     */
    public function install(PluginBundle $plugin)
    {
        $this->checkInstallationStatus($plugin, false);
        $this->validatePlugin($plugin);
        $this->log('Saving plugin configuration...');
        $this->recorder->register($plugin, $this->validator->getPluginConfiguration());
        $this->baseInstaller->install($plugin);
    }

    /**
     * Uninstalls a plugin.
     *
     * @param PluginBundle $plugin
     */
    public function uninstall(PluginBundle $plugin)
    {
        $this->checkInstallationStatus($plugin, true);
        $this->log('Removing plugin configuration...');
        $this->recorder->unregister($plugin);
        $this->baseInstaller->uninstall($plugin);
    }

    /**
     * Upgrades/downgrades a plugin to a specific version.
     *
     * @param PluginBundle $plugin
     * @param string       $currentVersion
     * @param string       $targetVersion
     */
    public function update(PluginBundle $plugin, $currentVersion, $targetVersion)
    {
        $this->checkInstallationStatus($plugin, true);
        $this->validator->activeUpdateMode();
        $this->validatePlugin($plugin);
        $this->validator->deactivateUpdateMode();
        $this->log('Updating plugin configuration...');
        $this->baseInstaller->update($plugin, $currentVersion, $targetVersion);
        $this->recorder->update($plugin, $this->validator->getPluginConfiguration());
    }

    private function checkInstallationStatus(PluginBundle $plugin, $shouldBeInstalled = true)
    {
        $this->log(sprintf('<fg=blue>Checking installation status for plugin %s</fg=blue>', $plugin->getName()));

        if ($this->recorder->isRegistered($plugin) !== $shouldBeInstalled) {
            $stateDiscr = $shouldBeInstalled ? 'not' : 'already';

            throw new \LogicException(
                "Plugin '{$plugin->getName()}' is {$stateDiscr} installed."
            );
        }
    }

    private function validatePlugin(PluginBundle $plugin)
    {
        $this->log('Validating plugin...');
        $errors = $this->validator->validate($plugin);

        if (0 !== count($errors)) {
            $report = "Plugin '{$plugin->getNamespace()}' cannot be installed, due to the "
                . "following validation errors :" . PHP_EOL;

            foreach ($errors as $error) {
                $report .= $error->getMessage() . PHP_EOL;
            }

            throw new \Exception($report);
        }
    }
}
