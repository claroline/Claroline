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

        /** @var ResourceType $coreType */
        $coreType = $om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(['name' => 'resource_shortcut']);

        if (!empty($coreType)) {
            /** @var ResourceType $pluginType */
            $pluginType = $om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneBy(['name' => 'shortcut']);

            // update old resource type
            $coreType->setName('shortcut');
            $coreType->setPlugin($pluginType->getPlugin());

            // remove extra type
            $om->remove($pluginType);
            $om->flush();
            
            // save updated type
            $om->persist($coreType);
            $om->flush();
        }
    }
}
