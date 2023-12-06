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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * This class is used to perform the (un-)installation of a plugin.
 */
class Installer implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var Validator */
    private $validator;
    /** @var Recorder */
    private $recorder;
    /** @var InstallationManager */
    private $baseInstaller;
    /** @var ObjectManager */
    private $om;
    /** @var VersionManager */
    private $versionManager;
    /** @var PluginManager */
    private $pluginManager;

    public function __construct(
        Validator $validator,
        Recorder $recorder,
        InstallationManager $installer,
        ObjectManager $om,
        PluginManager $pluginManager,
        VersionManager $versionManager
    ) {
        $this->validator = $validator;
        $this->recorder = $recorder;
        $this->baseInstaller = $installer;
        $this->om = $om;
        $this->pluginManager = $pluginManager;
        $this->versionManager = $versionManager;
    }

    /**
     * Installs a plugin.
     *
     * @throws \Exception if the plugin doesn't pass the validation
     */
    public function install(PluginBundleInterface $plugin)
    {
        $this->baseInstaller->install($plugin);

        $pluginEntity = $this->pluginManager->getPluginByShortName(
            $plugin->getName()
        );

        if (!$this->pluginManager->isReady($pluginEntity)) {
            $errors = $this->pluginManager->getMissingRequirements($pluginEntity);

            foreach ($errors['extensions'] as $extension) {
                $this->log(sprintf('<fg=red>Extension %s missing for %s !</fg=red>', $extension, $plugin->getName()));
            }

            foreach ($errors['plugins'] as $bundle) {
                $this->log(sprintf('<fg=red>The plugin %s is required for %s ! You must enable it first to use %s.</fg=red>', $bundle, $plugin->getName(), $plugin->getName()));
            }

            foreach ($errors['extras'] as $extra) {
                $this->log(sprintf('<fg=red>The plugin %s has extra requirements ! %s.</fg=red>', $plugin->getName(), $extra));
            }

            $this->log(sprintf('<fg=red>Disabling %s...</fg=red>', $plugin->getName()));
            $this->pluginManager->disable($pluginEntity);
        }

        $version = $this->versionManager->register($plugin);
        $this->versionManager->execute($version);
    }

    /**
     * Uninstalls a plugin.
     */
    public function uninstall(PluginBundleInterface $plugin)
    {
        $this->checkInstallationStatus($plugin, true);

        $this->log('Removing plugin configuration...');
        $this->recorder->unregister($plugin);
        $this->baseInstaller->uninstall($plugin);
    }

    /**
     * Upgrades/downgrades a plugin to a specific version.
     *
     * @param string $currentVersion
     * @param string $targetVersion
     */
    public function update(PluginBundleInterface $plugin, $currentVersion, $targetVersion)
    {
        $this->checkInstallationStatus($plugin, true);

        $this->baseInstaller->update($plugin, $currentVersion, $targetVersion);

        // updates plugin version
        $version = $this->versionManager->register($plugin);
        $this->versionManager->execute($version);
    }

    public function end(PluginBundleInterface $plugin, $currentVersion = null, $targetVersion = null)
    {
        $this->baseInstaller->end($plugin, $currentVersion, $targetVersion);
    }

    private function checkInstallationStatus(PluginBundleInterface $plugin, $shouldBeInstalled = true): void
    {
        $this->log(sprintf('<fg=blue>Checking installation status for plugin %s</fg=blue>', $plugin->getName()));

        if ($this->recorder->isRegistered($plugin) !== $shouldBeInstalled) {
            $stateDiscr = $shouldBeInstalled ? 'not' : 'already';

            throw new \LogicException("Plugin '{$plugin->getName()}' is {$stateDiscr} installed.");
        }
    }
}
