<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    private $bundlesFile;
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
        $entries = parse_ini_file($this->bundlesFile);
        $activeBundles = array();
        $configProviderBundles = array();
        $nonAutoConfigurableBundles = array();
        $environment = $this->getEnvironment();

        foreach ($entries as $bundleClass => $isActive) {
            if ($isActive && $bundleClass !== 'Claroline\KernelBundle\ClarolineKernelBundle') {
                $bundle = new $bundleClass($this->kernel);

                if ($bundle instanceof ConfigurationProviderInterface) {
                    $configProviderBundles[] = $bundle;
                }

                if (!$bundle instanceof AutoConfigurableInterface) {
                    $nonAutoConfigurableBundles[] = $bundle;
                } elseif ($bundle->supports($environment)) {
                    $activeBundles[] = array(
                        self::BUNDLE_INSTANCE => $bundle,
                        self::BUNDLE_CONFIG => $bundle->getConfiguration($environment)
                    );
                }
            }
        }

        foreach ($nonAutoConfigurableBundles as $bundle) {
            foreach ($configProviderBundles as $provider) {
                $config = $provider->suggestConfigurationFor($bundle, $environment);

                if ($config instanceof ConfigurationBuilder) {
                    $activeBundles[] = array(
                        self::BUNDLE_INSTANCE => $bundle,
                        self::BUNDLE_CONFIG => $config
                    );

                    break;
                }
            }
        }

        return $activeBundles;
    }

    private function __construct(KernelInterface $kernel, $bundlesFile)
    {
        if (!file_exists($bundlesFile)) {
            throw new \InvalidArgumentException("'{$bundlesFile}' does not exist");
        }

        $this->kernel = $kernel;
        $this->bundlesFile = $bundlesFile;
    }

    private function getEnvironment()
    {
        $environment = $this->kernel->getEnvironment();

        return preg_match('#tmp\d+#', $environment) ? 'dev' : $environment;
    }
}
