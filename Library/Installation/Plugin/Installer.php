<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Claroline\InstallationBundle\Bundle\BundleVersion;
use Claroline\CoreBundle\Library\PluginBundle;

/**
 * This class is used to perform the (un-)installation of a plugin.
 *
 * @DI\Service("claroline.plugin.installer")
 */
class Installer
{
    private $validator;
    private $recorder;
    private $baseInstaller;
    private $logger;

    /**
     * Constructor.
     *
     * @param Validator             $validator
     * @param Recorder              $recorder
     * @param InstallationManager   $installer
     *
     * @DI\InjectParams({
     *     "validator"      = @DI\Inject("claroline.plugin.validator"),
     *     "recorder"       = @DI\Inject("claroline.plugin.recorder"),
     *     "installer"      = @DI\Inject("claroline.installation.manager")
     * })
     */
    public function __construct(
        Validator $validator,
        Recorder $recorder,
        InstallationManager $installer
    )
    {
        $this->validator = $validator;
        $this->recorder = $recorder;
        $this->baseInstaller = $installer;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
        $this->baseInstaller->setLogger($logger);
    }

    /**
     * Installs a plugin.
     *
     * PluginBundle $plugin
     *
     * @throws Exception if the plugin doesn't pass the validation
     */
    public function install(PluginBundle $plugin)
    {
        $this->checkInstallationStatus($plugin, false);
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

        $this->baseInstaller->install($plugin);
        $this->log('Saving plugin configuration...');
        $this->recorder->register($plugin, $this->validator->getPluginConfiguration());
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
     * @param PluginBundle  $plugin
     * @param BundleVersion $current
     * @param BundleVersion $target
     */
    public function update(PluginBundle $plugin, BundleVersion $current, BundleVersion $target)
    {
        $this->checkInstallationStatus($plugin, true);
        $this->baseInstaller->update($plugin, $current, $target);
        // here come the plugin update tasks (e.g. config update)
    }

    private function checkInstallationStatus(PluginBundle $plugin, $shouldBeInstalled = true)
    {
        $this->log('Checking installation status...');

        if ($this->recorder->isRegistered($plugin) !== $shouldBeInstalled) {
            $stateDiscr = $shouldBeInstalled ? 'not' : 'already';

            throw new \LogicException(
                "Plugin '{$plugin->getName()}' is {$stateDiscr} installed."
            );
        }
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}

