<?php

namespace Claroline\LinkBundle\Installation;

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postInstall()
    {
        $this->log('Get `resource_shortcut` from core plugin...');

        $om = $this->container->get('claroline.persistence.object_manager');
        $conn = $this->container->get('doctrine.dbal.default_connection');

        /** @var ResourceType $coreType */
        $coreType = $om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(['name' => 'resource_shortcut']);

        if (!empty($coreType)) {
            /** @var ResourceType $pluginType */
            $pluginType = $om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneBy(['name' => 'shortcut']);

            $conn->query("DELETE FROM claro_resource_type WHERE name = 'shortcut'");
            $conn->query("
                UPDATE claro_resource_type SET 
                name = 'shortcut', 
                plugin_id = '".$pluginType->getPlugin()->getId()."',
                class = '".$pluginType->getClass()."'
                WHERE id = '".$coreType->getId()."'
            ");
        }
    }
}
