<?php

namespace Claroline\KernelBundle\Manager;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class BundleManager
{
    const BUNDLE_INSTANCE = 'instance';
    const BUNDLE_CONFIG = 'config';

    private $kernel;
    private $environment;
    private $bundlesFile;
    private $activeBundles;
    private static $selfInstance;

    public static function initialize(KernelInterface $kernel, $bundlesFile)
    {
        static::$selfInstance = new self($kernel, $bundlesFile);
    }

    public static function getInstance()
    {
        if (isset(static::$selfInstance)) {
            return static::$selfInstance;
        }

        throw new \LogicException(
            'Manager has not been initialized: call BundleManager::initialize() first'
        );
    }

    public function getActiveBundles()
    {
        return $this->activeBundles;
    }

    private function __construct(KernelInterface $kernel, $bundlesFile)
    {
        if (!is_file($bundlesFile)) {
            throw new \InvalidArgumentException("'{$bundlesFile}' is not a file");
        }

        $this->kernel = $kernel;
        $this->environment = $kernel->getEnvironment();
        $this->bundlesFile = $bundlesFile;
        $this->activeBundles = $this->initializeBundles();
    }

    private function initializeBundles()
    {
        $entries = parse_ini_file($this->bundlesFile);
        $activeBundles = array();
        $configProviderBundles = array();
        $nonAutoConfigurableBundles = array();

        foreach ($entries as $bundleClass => $isActive) {
            if ($isActive && $bundleClass !== 'Claroline\KernelBundle\ClarolineKernelBundle') {
                $bundle = new $bundleClass($this->kernel);

                if ($bundle instanceof ConfigurationProviderInterface) {
                    $configProviderBundles[] = $bundle;
                }

                if (!$bundle instanceof AutoConfigurableInterface) {
                    $nonAutoConfigurableBundles[] = $bundle;
                } elseif ($bundle->supports($this->environment)) {
                    $activeBundles[] = array(
                        self::BUNDLE_INSTANCE => $bundle,
                        self::BUNDLE_CONFIG => $bundle->getConfiguration($this->environment)
                    );
                }
            }
        }

        foreach ($nonAutoConfigurableBundles as $bundle) {
            foreach ($configProviderBundles as $provider) {
                $config = $provider->suggestConfigurationFor($bundle, $this->environment);

                if ($config instanceof ConfigurationBuilder) {
                    $activeBundles[] = array(
                        self::BUNDLE_INSTANCE => $bundle,
                        self::BUNDLE_CONFIG => $config
                    );
                }

                break;
            }
        }

        return $activeBundles;
    }
}
