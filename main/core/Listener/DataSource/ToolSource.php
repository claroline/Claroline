<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service
 */
class ToolSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * ToolSource constructor.
     *
     * @DI\InjectParams({
     *     "finder"           = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param FinderProvider   $finder
     * @param TokenStorage     $tokenStorage
     * @param WorkspaceManager $workspaceManager
     */
    public function __construct(FinderProvider $finder, TokenStorage $tokenStorage, WorkspaceManager $workspaceManager)
    {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @DI\Observe("data_source.tools.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        switch ($event->getContext()) {
            case DataSource::CONTEXT_DESKTOP:
                $user = $event->getUser();
                $options['hiddenFilters']['isDisplayableInDesktop'] = true;
                $options['hiddenFilters']['orderedToolType'] = 0;
                $options['hiddenFilters']['user'] = $user->getUuid();
                break;
            case DataSource::CONTEXT_WORKSPACE:
                $workspace = $event->getWorkspace();
                $isManager = $this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken());
                $options['hiddenFilters']['isDisplayableInWorkspace'] = true;
                $options['hiddenFilters']['orderedToolType'] = 0;
                $options['hiddenFilters']['workspace'] = $workspace->getUuid();

                if ($workspace->isPersonal()) {
                    $options['hiddenFilters']['personalWorkspace'] = true;
                }
                if (!$isManager) {
                    $user = $this->tokenStorage->getToken()->getUser();
                    $roles = 'anon.' === $user ?
                        ['ROLE_ANONYMOUS'] :
                        $user->getRoles();
                    $options['hiddenFilters']['roles'] = $roles;
                }
                break;
        }

        $event->setData($this->finder->search(Tool::class, $options));
        $event->stopPropagation();
    }
}
