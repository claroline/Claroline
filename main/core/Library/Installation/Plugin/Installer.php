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
use Claroline\CoreBundle\Library\PluginBundleInterface;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Manager\InstallationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
    private $versionManager;

    /**
     * Constructor.
     *
     * @param Validator           $validator
     * @param Recorder            $recorder
     * @param InstallationManager $installer
     *
     * @DI\InjectParams({
     *     "validator"     = @DI\Inject("claroline.plugin.validator"),
     *     "recorder"      = @DI\Inject("claroline.plugin.recorder"),
     *     "installer"     = @DI\Inject("claroline.installation.manager"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "pluginManager" = @DI\Inject("claroline.manager.plugin_manager"),
     *     "translator"    = @DI\Inject("translator"),
     *     "versionManager" = @DI\Inject("claroline.manager.version_manager")
     * })
     */
    public function __construct(
        Validator $validator,
        Recorder $recorder,
        InstallationManager $installer,
        ObjectManager $om,
        PluginManager $pluginManager,
        TranslatorInterface $translator,
        $versionManager
    ) {
        $this->validator = $validator;
        $this->recorder = $recorder;
        $this->baseInstaller = $installer;
        $this->om = $om;
        $this->pluginManager = $pluginManager;
        $this->translator = $translator;
        $this->versionManager = $versionManager;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->baseInstaller->setLogger($logger);
        $this->recorder->setLogger($logger);
    }

    /**
     * Installs a plugin.
     *
     * PluginBundleInterface $plugin
     *
     * @param PluginBundleInterface $plugin
     *
     * @throws \Exception if the plugin doesn't pass the validation
     */
    public function install(PluginBundleInterface $plugin)
    {
        $this->versionManager->setLogger($this->logger);
        $version = $this->versionManager->register($plugin);
        $this->checkInstallationStatus($plugin, false);
        $this->validatePlugin($plugin);
        $this->log('Saving configuration...');
        $pluginEntity = $this->recorder->register($plugin, $this->validator->getPluginConfiguration());
        $this->baseInstaller->install($plugin, false);

        if (!$this->pluginManager->isReady($pluginEntity) || !$this->pluginManager->isActivatedByDefault($pluginEntity)) {
            $errors = $this->pluginManager->getMissingRequirements($pluginEntity);

            foreach ($errors['extensions'] as $extension) {
                $this->log(sprintf('<fg=red>Extension %s missing for %s !</fg=red>', $extension, $plugin->getName()));
            }

            foreach ($errors['plugins'] as $bundle) {
                $this->log(sprintf('<fg=red>The plugin %s is required for %s ! You must enable it first to use %s.</fg=red>', $bundle, $plugin->getName(), $plugin->getName()));
            }

            foreach ($errors['extras'] as $extra) {
                $this->log(sprintf('<fg=red>The plugin %s has extra requirements ! %s.</fg=red>', $plugin->getName(), $this->translator->trans($extra, [], 'error')));
            }

            $this->log(sprintf('<fg=red>Disabling %s...</fg=red>', $plugin->getName()));
            $this->pluginManager->disable($pluginEntity);
        }

        $version = $this->versionManager->execute($version);
    }

    /**
     * Uninstalls a plugin.
     *
     * @param PluginBundleInterface $plugin
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
     * @param PluginBundleInterface $plugin
     * @param string                $currentVersion
     * @param string                $targetVersion
     */
    public function update(PluginBundleInterface $plugin, $currentVersion, $targetVersion)
    {
        $this->versionManager->setLogger($this->logger);
        $version = $this->versionManager->register($plugin);
        $this->checkInstallationStatus($plugin, true);
        $this->validator->activeUpdateMode();
        $this->validatePlugin($plugin);
        $this->validator->deactivateUpdateMode();
        $this->log('Updating plugin configuration...');
        $this->baseInstaller->update($plugin, $currentVersion, $targetVersion);
        $this->recorder->update($plugin, $this->validator->getPluginConfiguration());
        $this->versionManager->execute($version);
    }

    public function end(PluginBundleInterface $plugin)
    {
        $this->baseInstaller->end($plugin);
    }

    public function checkInstallationStatus(PluginBundleInterface $plugin, $shouldBeInstalled = true)
    {
        $this->log(sprintf('<fg=blue>Checking installation status for plugin %s</fg=blue>', $plugin->getName()));

        if ($this->recorder->isRegistered($plugin) !== $shouldBeInstalled) {
            $stateDiscr = $shouldBeInstalled ? 'not' : 'already';

            throw new \LogicException(
                "Plugin '{$plugin->getName()}' is {$stateDiscr} installed."
            );
        }
    }

    public function validatePlugin(PluginBundleInterface $plugin)
    {
        $this->log('Validating configuration...');
        $errors = $this->validator->validate($plugin);

        if (0 !== count($errors)) {
            $report = "Plugin '{$plugin->getNamespace()}' cannot be installed, due to the "
                .'following validation errors :'.PHP_EOL;

            foreach ($errors as $error) {
                $report .= $error->getMessage().PHP_EOL;
            }

            throw new \Exception($report);
        }
    }

    public function updateAllConfigurations()
    {
        $bundles = $this->pluginManager->getInstalledBundles();

        foreach ($bundles as $bundle) {
            $this->log('Updating configuration for '.get_class($bundle['instance']));
            $this->validator->activeUpdateMode();
            $this->validatePlugin($bundle['instance']);
            $this->validator->deactivateUpdateMode();
            $this->log('Plugin validated: proceed to database changes...');
            $this->om->clear();
            $this->recorder->update($bundle['instance'], $this->validator->getPluginConfiguration());
        }
    }
}
