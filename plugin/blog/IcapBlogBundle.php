<?php

namespace Icap\BlogBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\BlogBundle\Installation\AdditionalInstaller;

class IcapBlogBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config
            ->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_blog')
            ->addContainerResource($this->getPath().'/Resources/config/twig.yml')
            ->addContainerResource($this->getPath().'/Resources/config/parameters.yml');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredPlugins()
    {
        return ['Icap\\NotificationBundle\\IcapNotificationBundle'];
    }
}
