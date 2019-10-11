<?php

namespace Claroline\HistoryBundle\Listener;

use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var HistoryManager */
    private $manager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * ResourceListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param HistoryManager        $manager
     * @param WorkspaceManager      $workspaceManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        HistoryManager $manager,
        WorkspaceManager $workspaceManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @param OpenWorkspaceEvent $event
     */
    public function onOpen(OpenWorkspaceEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' !== $user && !$this->workspaceManager->isImpersonated($this->tokenStorage->getToken())) {
            $this->manager->addWorkspace($event->getWorkspace(), $user);
        }
    }
}
