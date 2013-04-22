<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use \LogicException;
use Symfony\Component\HttpKernel\KernelInterface;
use JMS\DiExtraBundle\Annotation as DI;

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
     * @param Loader            $loader
     * @param Validator         $validator
     * @param Migrator          $migrator
     * @param Recorder          $recorder
     * @param KernelInterface   $kernel
     *
     * @DI\InjectParams({
     *     "loader" = @DI\Inject("claroline.plugin.loader"),
     *     "validator" = @DI\Inject("claroline.plugin.validator"),
     *     "migrator" = @DI\Inject("claroline.plugin.migrator"),
     *     "recorder" = @DI\Inject("claroline.plugin.recorder"),
     *     "kernel" = @DI\Inject("kernel")
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
     * Sets the plugin loader.
     *
     * @param Loader $loader
     */
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Sets the plugin validator.
     *
     * @param Validator $validator
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Sets the plugin recorder.
     *
     * @param Recorder $recorder
     */
    public function setRecorder(Recorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Sets the plugin migrator.
     *
     * @param Migrator $migrator
     */
    public function setMigrator(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }

    /**
     * Installs a plugin.
     *
     * @param string $pluginFqcn
     *
     * @throws Exception if the plugin doesn't pass the validation
     */
    public function install($pluginFqcn)
    {
        $this->checkRegistrationStatus($pluginFqcn, false);
        $plugin = $this->loader->load($pluginFqcn);
        $errors = $this->validator->validate($plugin);

        if (0 === count($errors)) {
            $config = $this->validator->getPluginConfiguration();
            $this->migrator->install($plugin);
            $this->recorder->register($plugin, $config);
            $this->kernel->shutdown();
            $this->kernel->boot();
        } else {
            $report = "Plugin '{$pluginFqcn}' cannot be installed, due to the "
                . "following validation errors :" . PHP_EOL;

            foreach ($errors as $error) {
                $report .= $error->getMessage() . PHP_EOL;
            }

            throw new RuntimeException($report);
        }
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
}