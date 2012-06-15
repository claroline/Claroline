<?php

namespace Claroline\CoreBundle\Library\Plugin;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PlayerDependencyInjection extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->setService($container);
    }

    protected function setUp($container)
    {
        $taggedServices = $container->findTaggedServiceIds('resource.player');
        $serviceArray = $container->getParameter('claroline.resource_players');

        foreach($taggedServices as $name => $service) {
            $serviceArray[$name] = '';
        }

        $container->setParameter('claroline.resource_players', $serviceArray);
    }
}