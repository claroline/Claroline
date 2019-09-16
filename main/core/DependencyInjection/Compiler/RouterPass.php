<?php

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Claroline\CoreBundle\Routing\ClaroRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('router', ClaroRouter::class);
    }
}
