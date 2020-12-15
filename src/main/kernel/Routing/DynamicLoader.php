<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Routing;

use Symfony\Component\Config\Loader\Loader as Loader;
use Symfony\Component\Routing\RouteCollection;
use Claroline\KernelBundle\Manager\BundleManager;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class DynamicLoader extends Loader
{
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        foreach (BundleManager::getInstance()->getActiveBundles() as $bundle) {
            foreach ($bundle[BundleManager::BUNDLE_CONFIG]->getRoutingResources() as $resource) {
                $subCollection = $this->import(
                    $resource[ConfigurationBuilder::RESOURCE_OBJECT],
                    $resource[ConfigurationBuilder::RESOURCE_TYPE]
                );

                if (!empty($resource[ConfigurationBuilder::ROUTING_PREFIX])) {
                    $subCollection->addPrefix($resource[ConfigurationBuilder::ROUTING_PREFIX]);
                }

                $collection->addCollection($subCollection);
            }
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'dynamic';
    }
}
