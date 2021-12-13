<?php

namespace UJM\ExoBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\TagBundle\ClarolineTagBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UJM\ExoBundle\DependencyInjection\Compiler\ItemDefinitionsPass;

class UJMExoBundle extends DistributionPluginBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ItemDefinitionsPass());
    }

    public function getRequiredPlugins()
    {
        return [
            ClarolineTagBundle::class,
        ];
    }
}
