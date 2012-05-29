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
        $taggedServices = $container->findTaggedServiceIds("resource.player");
        

        $serviceArray = $container->getParameter('player.service.list');
        //$names = array_keys($serviceArray);

        foreach($taggedServices as $name => $service)
        {
            $serviceArray[$name]='';
        }
        /*
        foreach($names as $name)
        {
            $serviceArray[$name]='';
        }
        */
        
        $container->setParameter("player.service.list", $serviceArray);
    }
}