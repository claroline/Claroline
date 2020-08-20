<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle;

use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Manager\BundleManager;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

//When doing a unique depot, we should move this to the AppKernel class
class ClarolineKernelBundle extends Bundle
{
    /** @var BundleManager */
    private $bundleManager;
    /** @var KernelInterface */
    private $kernel;
    private $bundlesFile;

    public function __construct(KernelInterface $kernel, $bundlesFile = null)
    {
        $this->kernel = $kernel;

        if (!$bundlesFile) {
            $bundlesFile = $kernel->getProjectDir().'/app/config/bundles.ini';
        }

        $this->bundlesFile = $bundlesFile;

        BundleManager::initialize($kernel, $bundlesFile);
        $this->bundleManager = BundleManager::getInstance();
    }

    public function getBundles($includeSelf = true)
    {
        $bundles = [];

        foreach ($this->bundleManager->getActiveBundles('test' === $this->kernel->getEnvironment()) as $bundle) {
            $bundles[] = $bundle[BundleManager::BUNDLE_INSTANCE];
        }

        if ($includeSelf) {
            $bundles[] = $this;
        }

        return $bundles;
    }

    public function loadConfigurations(LoaderInterface $loader)
    {
        foreach ($this->bundleManager->getActiveBundles('test' === $this->kernel->getEnvironment()) as $bundle) {
            foreach ($bundle[BundleManager::BUNDLE_CONFIG]->getContainerResources() as $resource) {
                $loader->load(
                    $resource[ConfigurationBuilder::RESOURCE_OBJECT],
                    $resource[ConfigurationBuilder::RESOURCE_TYPE]
                );
            }
        }
    }
}
