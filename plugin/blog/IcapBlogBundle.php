<?php

namespace Icap\BlogBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\BlogBundle\Installation\AdditionalInstaller;

class IcapBlogBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        $config
            ->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_blog')
            ->addContainerResource($this->getPath().'/Resources/config/twig.yml')
            ->addContainerResource($this->getPath().'/Resources/config/parameters.yml')
        ;

        return $config;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
