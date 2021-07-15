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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * The KernelBundle probably should do all of that.
 */
class BundleManager implements LoggerAwareInterface
{
    use LoggableTrait;

    const BUNDLE_INSTANCE = 'instance';
    const BUNDLE_CONFIG = 'config';

    private $env;
    private $bundlesFile;

    private static $selfInstance;

    public static function initialize(string $env, string $bundlesFile)
    {
        static::$selfInstance = new self($env, $bundlesFile);
    }

    public static function getInstance()
    {
        if (isset(static::$selfInstance)) {
            return static::$selfInstance;
        }

        throw new \LogicException('Manager has not been initialized: call BundleManager::initialize() first');
    }

    public function getActiveBundles($fetchAll = false)
    {
        $entries = parse_ini_file($this->bundlesFile);
        $activeBundles = [];
        $configProviderBundles = [];
        $nonAutoConfigurableBundles = [];
        $environment = $this->env;

        foreach ($entries as $bundleClass => $isActive) {
            if ((bool) $isActive || $fetchAll) {
                if (class_exists($bundleClass)) {
                    /** @var BundleInterface $bundle */
                    $bundle = new $bundleClass();

                    if ($bundle instanceof PluginBundleInterface) {
                        foreach ($bundle->getRequiredThirdPartyBundles($environment) as $thirdPartyBundle) {
                            $nonAutoConfigurableBundles[\get_class($thirdPartyBundle)] = $thirdPartyBundle;
                        }
                    }

                    if ($bundle instanceof ConfigurationProviderInterface) {
                        $configProviderBundles[] = $bundle;
                    }

                    if (!$bundle instanceof AutoConfigurableInterface) {
                        $nonAutoConfigurableBundles[$bundleClass] = $bundle;
                    } elseif ($bundle->supports($environment)) {
                        $activeBundles[] = [
                          self::BUNDLE_INSTANCE => $bundle,
                          self::BUNDLE_CONFIG => $bundle->getConfiguration($environment),
                      ];
                    }
                } else {
                    $this->log("Class {$bundleClass} was not loaded");
                }
            }
        }

        foreach ($nonAutoConfigurableBundles as $bundle) {
            foreach ($configProviderBundles as $provider) {
                $config = $provider->suggestConfigurationFor($bundle, $environment);

                if ($config instanceof ConfigurationBuilder) {
                    $activeBundles[] = [
                        self::BUNDLE_INSTANCE => $bundle,
                        self::BUNDLE_CONFIG => $config,
                    ];

                    break;
                }
            }
        }

        return $activeBundles;
    }

    private function __construct(string $env, $bundlesFile)
    {
        if (!file_exists($bundlesFile)) {
            throw new \InvalidArgumentException("'{$bundlesFile}' does not exist");
        }

        $this->env = $env;
        $this->bundlesFile = $bundlesFile;
    }
}
