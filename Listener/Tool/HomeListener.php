<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\ExportToolEvent;
use Claroline\CoreBundle\Event\ImportToolEvent;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class HomeListener
{
    private $workspaceManager;
    private $homeTabManager;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed"                 = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"         = @DI\Inject("templating"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "securityContext"    = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        $em,
        $ed,
        $templating,
        WorkspaceManager $workspaceManager,
        HomeTabManager $homeTabManager,
        SecurityContextInterface $securityContext
    )
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->workspaceManager = $workspaceManager;
        $this->homeTabManager = $homeTabManager;
        $this->securityContext = $securityContext;
    }

    /**
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopHome(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopHome());
    }

    /**
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceHome(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceHome($event->getWorkspace()->getId()));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("tool_home_from_template")
     *
     * @param ImportToolEvent $event
     */
    public function onImportHome(ImportToolEvent $event)
    {
        //no implementation yet
    }

    /**
     * @DI\Observe("tool_home_to_template")
     *
     * @param ExportToolEvent $event
     */
    public function onExportHome(ExportToolEvent $event)
    {
        //no implementation yet
        $home = array();
        $event->setConfig($home);
    }

    /**
     * Renders the home page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceHome($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);
        $tabId = 0;

        $firstHomeTab = reset($workspaceHomeTabConfigs);

        if ($firstHomeTab) {
            $tabId = $firstHomeTab->getHomeTab()->getId();
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabsWithoutConfig.html.twig',
            array(
                'workspace' => $workspace,
                'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
                'tabId' => $tabId
            )
        );
    }

    /**
     * Displays the first desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopHome()
    {
        $user = $this->securityContext->getToken()->getUser();
        $adminHomeTabConfigs = $this->homeTabManager
            ->generateAdminHomeTabConfigsByUser($user);
        $visibleAdminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigs);
        $userHomeTabConfigs = $this->homeTabManager
            ->getVisibleDesktopHomeTabConfigsByUser($user);
        $tabId = 0;

        $firstAdminHomeTab = reset($visibleAdminHomeTabConfigs);

        if ($firstAdminHomeTab) {
            $tabId = $firstAdminHomeTab->getHomeTab()->getId();
        } else {
            $firstHomeTab = reset($userHomeTabConfigs);

            if ($firstHomeTab) {
                $tabId = $firstHomeTab->getHomeTab()->getId();
            }
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabsWithoutConfig.html.twig',
            array(
                'adminHomeTabConfigs' => $visibleAdminHomeTabConfigs,
                'userHomeTabConfigs' => $userHomeTabConfigs,
                'tabId' => $tabId
            )
        );
    }
}
