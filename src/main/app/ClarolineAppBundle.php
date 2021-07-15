<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle;

use Claroline\AppBundle\DependencyInjection\Compiler\ApiConfigPass;
use Claroline\AppBundle\DependencyInjection\Compiler\RouterPass;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ClarolineAppBundle extends Bundle implements AutoConfigurableInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ApiConfigPass());
        $container->addCompilerPass(new RouterPass());
    }

    public function supports($environment)
    {
        return true;
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
    }

    public function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getPath().'/Resources/config/routing.yml');
    }
}
