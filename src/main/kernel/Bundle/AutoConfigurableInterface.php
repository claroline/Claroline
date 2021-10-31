<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Bundle;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\RouteCollectionBuilder;

interface AutoConfigurableInterface
{
    public function supports(string $environment): bool;

    public function configureRoutes(RouteCollectionBuilder $routes);

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader);

    /**
     * @return BundleInterface[] A list of bundle instances required by the bundle
     */
    public function getRequiredBundles(string $environment): array;
}
