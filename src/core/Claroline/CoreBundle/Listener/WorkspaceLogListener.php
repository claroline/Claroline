<?php

namespace Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceLog;
use Claroline\CoreBundle\Library\Event\WorkspaceLogEvent;

/**
 * @DI\Service
 */
class WorkspaceLogListener
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @DI\Observe("log_workspace_access")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onLogWorkspaceAccess(WorkspaceLogEvent $event)
    {
        if ($event->getType() === WorkspaceLogEvent::ACCESS_ACTION) {
            $wsLog = new WorkspaceLog();
            $wsLog->setType($event->getType());
            $wsLog->setUser($event->getUser());
            $wsLog->setWorkspace($event->getWorkspace());
            $wsLog->setData($event->getData());
            $this->em->persist($wsLog);
            $this->em->flush();
        }
    }
}