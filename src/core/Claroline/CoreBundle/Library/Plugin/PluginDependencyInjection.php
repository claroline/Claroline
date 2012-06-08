<?php

namespace Claroline\CoreBundle\Library\Plugin;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginDependencyInjection extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->setService($container);
    }
    
    protected function setUp($container)
    {
        $taggedServices = $container->findTaggedServiceIds("resource.manager");
        $serviceArray = $container->getParameter('resource.service.list');
         
        foreach($taggedServices as $name => $service)
        {
            $serviceArray[$name]='';
        }
        
        $container->setParameter("resource.service.list", $serviceArray);
    }
}