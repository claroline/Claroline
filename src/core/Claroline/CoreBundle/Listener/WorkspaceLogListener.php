<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\Workspace\WorkspaceLog;
use Claroline\CoreBundle\Library\Event\WorkspaceLogEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class WorkspaceLogListener extends ContainerAware
{
    public function onLogWorkspaceAccess(WorkspaceLogEvent $event)
    {
        $wsLog = new WorkspaceLog();

        if ($event->getType() === WorkspaceLogEvent::ACCESS_ACTION) {
            $wsLog->setType($event->getType());
            $wsLog->setUser($event->getUser());
            $wsLog->setWorkspace($event->getWorkspace());
            $wsLog->setData($event->getData());
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($wsLog);
            $em->flush();
        }
    }
}