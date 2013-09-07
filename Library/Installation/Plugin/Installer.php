<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use \LogicException;
use Symfony\Component\HttpKernel\KernelInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\PluginBundle;

/**
 * This class is used to perform the (un-)installation of a plugin. It uses
 * several dedicated components to load, validate and register the plugin.
 *
 * @DI\Service("claroline.plugin.installer")
 */
class Installer
{
    private $loader;
    private $validator;
    private $recorder;
    private $migrator;
    private $kernel;

    /**
     * Constructor.
     *
     * @param Loader          $loader
     * @param Validator       $validator
     * @param Migrator        $migrator
     * @param Recorder        $recorder
     * @param KernelInterface $kernel
     *
     * @DI\InjectParams({
     *     "loader"         = @DI\Inject("claroline.plugin.loader"),
     *     "validator"      = @DI\Inject("claroline.plugin.validator"),
     *     "migrator"       = @DI\Inject("claroline.plugin.migrator"),
     *     "recorder"       = @DI\Inject("claroline.plugin.recorder"),
     *     "kernel"         = @DI\Inject("kernel")
     * })
     */
    public function __construct(
        Loader $loader,
        Validator $validator,
        Migrator $migrator,
        Recorder $recorder,
        KernelInterface $kernel
    )
    {
        $this->loader = $loader;
        $this->validator = $validator;
        $this->migrator = $migrator;
        $this->recorder = $recorder;
        $this->kernel = $kernel;
    }

    /**
     * Installs a plugin.
     *
     * @param string $pluginFqcn    FQCN of the plugin bundle class
     * @param string $pluginPath    Path of the plugin bundle class
     *
     * @throws Exception if the plugin doesn't pass the validation
     */
    public function install($pluginFqcn, $pluginPath = null)
    {
        $this->checkRegistrationStatus($pluginFqcn, false);
        $plugin = $this->loader->load($pluginFqcn, $pluginPath);
        $errors = $this->validator->validate($plugin);

        if (0 !== count($errors)) {
            $report = "Plugin '{$pluginFqcn}' cannot be installed, due to the "
                . "following validation errors :" . PHP_EOL;

            foreach ($errors as $error) {
                $report .= $error->getMessage() . PHP_EOL;
            }

            throw new RuntimeException($report);
        }

        $config = $this->validator->getPluginConfiguration();
        $this->recorder->register($plugin, $config);
        $this->kernel->shutdown();
        $this->kernel->boot();
        $this->migrator->install($plugin);
        $this->loadFixtures($plugin);
    }

    /**
     * Uninstalls a plugin.
     *
     * @param string $pluginFqcn
     */
    public function uninstall($pluginFqcn)
    {
        $this->checkRegistrationStatus($pluginFqcn, true);
        $plugin = $this->loader->load($pluginFqcn);
        $this->recorder->unregister($plugin);
        $this->migrator->remove($plugin);
        $this->kernel->shutdown();
        $this->kernel->boot();
    }

    /**
     * Upgrades/downgrades a plugin to a specific version.
     *
     * @param string $pluginFqcn
     * @param string $version
     */
    public function migrate($pluginFqcn, $version)
    {
        $this->checkRegistrationStatus($pluginFqcn, true);
        $plugin = $this->loader->load($pluginFqcn);
        $this->migrator->migrate($plugin, $version);
    }

    /**
     * Checks if a plugin is installed.
     *
     * @param type $pluginFqcn
     *
     * @return boolean
     */
    public function isInstalled($pluginFqcn)
    {
        return $this->recorder->isRegistered($pluginFqcn);
    }

    private function checkRegistrationStatus($pluginFqcn, $expectedStatus)
    {
        if ($this->isInstalled($pluginFqcn) !== $expectedStatus) {
            $expectedStatus === true ? $stateDiscr = 'not' : $stateDiscr = 'already';

            throw new LogicException(
                "Plugin '{$pluginFqcn}' is {$stateDiscr} registered."
            );
        }
    }

    private function loadFixtures(PluginBundle $plugin)
    {
        $container = $this->kernel->getContainer();
        $mappingLoader = $container->get('claroline.installation.mapping_loader');
        $fixtureLoader = $container->get('claroline.installation.fixture_loader');
        $mappingLoader->registerMapping($plugin);
        $fixtureLoader->load($plugin);
    }
}

