<?php

namespace Claroline\KernelBundle;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Claroline\KernelBundle\Manager\BundleManager;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class ClarolineKernelBundle extends Bundle
{
    private $bundleManager;

    public function __construct(KernelInterface $kernel, $bundlesFile = null)
    {
        if (!$bundlesFile) {
            $bundlesFile = $kernel->getRootDir() . '/config/bundles.ini';
        }

        BundleManager::initialize($kernel, $bundlesFile);
        $this->bundleManager = BundleManager::getInstance();
    }

    public function getBundles($includeSelf = true)
    {
        $bundles = array();

        foreach ($this->bundleManager->getActiveBundles() as $bundle) {
            $bundles[] = $bundle[BundleManager::BUNDLE_INSTANCE];
        }

        if ($includeSelf) {
            $bundles[] = $this;
        }

        return $bundles;
    }

    public function loadConfigurations(LoaderInterface $loader)
    {
        foreach ($this->bundleManager->getActiveBundles() as $bundle) {
            foreach ($bundle[BundleManager::BUNDLE_CONFIG]->getContainerResources() as $resource) {
                $loader->load(
                    $resource[ConfigurationBuilder::RESOURCE_OBJECT],
                    $resource[ConfigurationBuilder::RESOURCE_TYPE]
                );
            }
        }
    }
}
