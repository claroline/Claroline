<?php

namespace UJM\ExoBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UJM\ExoBundle\DependencyInjection\Compiler\ItemDefinitionsPass;
use UJM\ExoBundle\Installation\AdditionalInstaller;

class UJMExoBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ItemDefinitionsPass());
    }

    public function getRequiredPlugins()
    {
        return ['Claroline\\TagBundle\\ClarolineTagBundle'];
    }
}
