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
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * The KernelBundle probably should do all of that.
 */
class BundleManager implements LoggerAwareInterface
{
    use LoggableTrait;

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

    public function getActiveBundles(?bool $fetchAll = false): array
    {
        $entries = parse_ini_file($this->bundlesFile);

        $bundles = [];
        foreach ($entries as $bundleClass => $isActive) {
            if ((bool) $isActive || $fetchAll) {
                if (class_exists($bundleClass)) {
                    /** @var BundleInterface $bundle */
                    $bundle = new $bundleClass();

                    if (!$bundle instanceof AutoConfigurableInterface) {
                        $bundles[$bundleClass] = $bundle;
                    } elseif ($bundle->supports($this->env)) {
                        $bundles[] = $bundle;
                    }

                    if ($bundle instanceof PluginBundleInterface) {
                        foreach ($bundle->getRequiredThirdPartyBundles($this->env) as $thirdPartyBundle) {
                            $bundles[\get_class($thirdPartyBundle)] = $thirdPartyBundle;
                        }
                    }
                } else {
                    $this->log("Class {$bundleClass} was not loaded");
                }
            }
        }

        return $bundles;
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
