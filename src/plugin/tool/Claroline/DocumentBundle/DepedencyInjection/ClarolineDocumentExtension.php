<?php

namespace Claroline\DocumentBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $env = $container->get('kernel')->getEnvironment();
        $filePath = __DIR__."/../files";
        
        if($env == "test")
        {
            $container->setParameter('claroline.files.directory', $filePath);
        }
    }
}