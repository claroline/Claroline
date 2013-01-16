<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLogEvent;
use Claroline\CoreBundle\Entity\Logger\ResourceLog;

class LogListener extends ContainerAware
{
    public function onLogResource(ResourceLogEvent $event)
    {
        $rs = new ResourceLog();
        if ($event->getAction() !== ResourceLogEvent::DELETE_ACTION){
            $rs->setResource($event->getResource());
        }

        $token = $this->container->get('security.context')->getToken();

        if ($token == null) {
            $user = $event->getResource()->getCreator();
        } else {
            $user = $token->getUser();
        }
        $rs->setCreator($event->getResource()->getCreator());
        $rs->setUpdator($user);
        $rs->setAction($event->getAction());
        $rs->setLogDescription($event->getLogDescription());
        $rs->setPath($event->getResource()->getPathForDisplay());
        $rs->setResourceType($event->getResource()->getResourceType());
        $rs->setWorkspace($event->getResource()->getWorkspace());
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($rs);
        $em->flush();
        return;
    }
}