<?php

namespace Claroline\HistoryBundle\Listener;

use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SecurityManager */
    private $securityManager;

    /** @var HistoryManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SecurityManager $securityManager,
        HistoryManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->securityManager = $securityManager;
        $this->manager = $manager;
    }

    public function onOpen(OpenWorkspaceEvent $event)
    {
        if (!$this->securityManager->isAnonymous() && !$this->securityManager->isImpersonated()) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $this->manager->addWorkspace($event->getWorkspace(), $user);
        }
    }
}
