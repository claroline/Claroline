<?php

namespace Claroline\AppBundle\DependencyInjection\Compiler;

use Claroline\AppBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('router', Router::class)->setPublic(true);
    }
}
