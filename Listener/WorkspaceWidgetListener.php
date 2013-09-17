<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(scope="request")
 */
class WorkspaceWidgetListener
{
    private $securityContext;
    private $templating;
    private $utils;
    private $workspaceManager;
    private $workspaceTagManager;

    /**
     * @DI\InjectParams({
     *     "securityContext"        = @DI\Inject("security.context"),
     *     "templating"             = @DI\Inject("templating"),
     *     "utils"                  = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager"    = @DI\Inject("claroline.manager.workspace_tag_manager")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager
    )
    {
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
    }

    /**
     * @DI\Observe("widget_my_workspaces_desktop")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $token = $this->securityContext->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);
        $datas = $this->workspaceTagManager
            ->getDatasForWorkspaceListByUser($user, $roles);

        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:desktopWidgetMyWorkspaces.html.twig',
            array(
                'workspaces' => $datas['workspaces']
            )
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}