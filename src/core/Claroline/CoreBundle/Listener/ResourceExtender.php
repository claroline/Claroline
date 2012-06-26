<?php

namespace Claroline\CoreBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class ResourceExtender implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(Events::loadClassMetadata);
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $classMetadata = $event->getClassMetadata();

        if ($classMetadata->getName() == 'Claroline\CoreBundle\Entity\Resource\AbstractResource') {
            $pluginTypes = $event->getEntityManager()
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
                ->findPluginResourceNameFqcns();

            foreach ($pluginTypes as $pluginType) {
                if ($pluginType['class'] != '') {
                    $classMetadata->discriminatorMap[$pluginType['class']] = $pluginType['class'];
                    $classMetadata->subClasses[] = $pluginType['class'];
                }
            }
        }
    }
}