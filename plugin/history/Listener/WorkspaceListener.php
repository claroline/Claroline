<?php

namespace Claroline\HistoryBundle\Listener;

use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\HistoryBundle\Manager\HistoryManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service()
 */
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
     * @DI\InjectParams({
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "manager"          = @DI\Inject("claroline.manager.history"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
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
     * @DI\Observe("workspace.open")
     *
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
