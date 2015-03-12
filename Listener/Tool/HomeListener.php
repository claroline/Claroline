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
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class HomeListener
{
    private $container;
    private $httpKernel;
    private $homeTabManager;
    private $securityContext;
    private $templating;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "container"          = @DI\Inject("service_container"),
     *     "httpKernel"         = @DI\Inject("http_kernel"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "templating"         = @DI\Inject("templating"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $httpKernel,
        HomeTabManager $homeTabManager,
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        WorkspaceManager $workspaceManager
    )
    {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->homeTabManager = $homeTabManager;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopHome(DisplayToolEvent $event)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:Tool\Home:displayDesktopHomeTab',
            'tabId' => -1
        );
        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
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
