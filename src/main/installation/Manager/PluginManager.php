<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Installation\Plugin\Recorder;
use Claroline\CoreBundle\Library\Installation\Plugin\Validator;
use Claroline\CoreBundle\Manager\PluginManager as BasePluginManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This class is used to perform the (un-)installation of a plugin.
 */
class PluginManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Validator $validator,
        private readonly Recorder $recorder,
        private readonly BundleManager $baseInstaller,
        private readonly ObjectManager $om,
        private readonly BasePluginManager $pluginManager,
        private readonly VersionManager $versionManager
    ) {
    }

    public function install(PluginBundleInterface $plugin): void
    {
        $this->baseInstaller->install($plugin);

        $pluginEntity = $this->pluginManager->getPluginByShortName(
            $plugin->getName()
        );

        if (!$this->pluginManager->isReady($pluginEntity)) {
            $errors = $this->pluginManager->getMissingRequirements($pluginEntity);

            foreach ($errors['extensions'] as $extension) {
                $this->logger->error(sprintf('Extension %s missing for %s !', $extension, $plugin->getName()));
            }

            foreach ($errors['plugins'] as $bundle) {
                $this->logger->error(sprintf('The plugin %s is required for %s ! You must enable it first to use %s.', $bundle, $plugin->getName(), $plugin->getName()));
            }

            foreach ($errors['extras'] as $extra) {
                $this->logger->error(sprintf('The plugin %s has extra requirements ! %s.', $plugin->getName(), $extra));
            }

            $this->logger->critical(sprintf('Disabling %s...', $plugin->getName()));
            $this->pluginManager->disable($pluginEntity);
        }

        $version = $this->versionManager->register($plugin);
        $this->versionManager->execute($version);
    }

    public function update(PluginBundleInterface $plugin, string $currentVersion, string $targetVersion): void
    {
        $this->checkInstallationStatus($plugin, true);

        $this->baseInstaller->update($plugin, $currentVersion, $targetVersion);

        // updates plugin version
        $version = $this->versionManager->register($plugin);
        $this->versionManager->execute($version);
    }

    public function end(PluginBundleInterface $plugin, string $currentVersion = null, string $targetVersion = null): void
    {
        $this->baseInstaller->end($plugin, $currentVersion, $targetVersion);
    }

    public function uninstall(PluginBundleInterface $plugin): void
    {
        $this->checkInstallationStatus($plugin, true);

        $this->logger->info('Removing plugin configuration...');
        $this->recorder->unregister($plugin);
        $this->baseInstaller->uninstall($plugin);
    }

    private function checkInstallationStatus(PluginBundleInterface $plugin, bool $shouldBeInstalled): void
    {
        $this->logger->info(sprintf('Checking installation status for plugin %s', $plugin->getName()));

        if ($this->recorder->isRegistered($plugin) !== $shouldBeInstalled) {
            $state = $shouldBeInstalled ? 'not' : 'already';

            throw new \LogicException("Plugin '{$plugin->getName()}' is $state installed.");
        }
    }
}
