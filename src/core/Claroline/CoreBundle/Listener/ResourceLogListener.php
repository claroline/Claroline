<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Event\ResourceLogEvent;
use Claroline\CoreBundle\Entity\Logger\ResourceLog;

/**
 * @DI\Service
 */
class ResourceLogListener
{
    private $em;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "context"    = @DI\Inject("security.context")
     * })
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, SecurityContextInterface $context)
    {
        $this->em = $em;
        $this->securityContext = $context;
    }

    /**
     * @DI\Observe("log_resource")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onLogResource(ResourceLogEvent $event)
    {
        $rs = new ResourceLog();

        if ($event->getAction() !== ResourceLogEvent::DELETE_ACTION) {
            $rs->setResource($event->getResource());
        }

        $token = $this->securityContext->getToken();

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
        $this->em->persist($rs);
        $this->em->flush();
    }
}