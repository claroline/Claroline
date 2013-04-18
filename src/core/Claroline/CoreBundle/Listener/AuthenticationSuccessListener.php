<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Event\LogUserLoginEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class AuthenticationSuccessListener
{
    private $securityContext;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "context"    = @DI\Inject("security.context"),
     *     "ed"         = @DI\Inject("event_dispatcher")
     * })
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(SecurityContextInterface $context, $ed)
    {
        $this->securityContext = $context;
        $this->eventDispatcher = $ed;
    }

    /**
     * @DI\Observe("security.interactive_login")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onAuthenticationSuccess()
    {
        $user = $this->securityContext->getToken()->getUser();
        $log = new LogUserLoginEvent($user);
        $this->eventDispatcher->dispatch('log', $log);
    }
}