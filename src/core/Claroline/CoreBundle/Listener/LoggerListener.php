<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLoggerEvent;
use Claroline\CoreBundle\Entity\Logger\ResourceLogger;

class LoggerListener extends ContainerAware
{
    public function onLogResource(ResourceLoggerEvent $event)
    {
        $rs = new ResourceLogger();
        if ($event->getAction() !== ResourceLoggerEvent::DELETE_ACTION){
            $rs->setInstance($event->getInstance());
        }

        $token = $this->container->get('security.context')->getToken();

        if ($token == null) {
            $user = $event->getInstance()->getResource()->getCreator();
        } else {
            $user = $token->getUser();
        }
        $rs->setCreator($event->getInstance()->getResource()->getCreator());
        $rs->setUpdator($user);
        $rs->setAction($event->getAction());
        $rs->setLogDescription($event->getLogDescription());
        $rs->setPath($event->getInstance()->getPathForDisplay());
        $rs->setResourceType($event->getInstance()->getResourceType());
        $rs->setWorkspace($event->getInstance()->getWorkspace());
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($rs);
        $em->flush();
        return;
    }
}