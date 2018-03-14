<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class ToolListener
{
    private $container;
    private $httpKernel;
    private $rightsManager;
    private $router;
    private $tokenStorage;
    private $templating;
    private $toolManager;
    private $translator;
    private $workspaceManager;
    const R_U = 'ROLE_USER';
    const R_A = 'ROLE_ADMIN';

    /**
     * @DI\InjectParams({
     *     "container"        = @DI\Inject("service_container"),
     *     "httpKernel"       = @DI\Inject("http_kernel"),
     *     "rightsManager"    = @DI\Inject("claroline.manager.rights_manager"),
     *     "router"           = @DI\Inject("router"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "templating"       = @DI\Inject("templating"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        $httpKernel,
        RightsManager $rightsManager,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        $templating,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    ) {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->rightsManager = $rightsManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @DI\Observe("open_tool_workspace_parameters")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceParameters(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceParameters($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_workspace_logs")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceLogs(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceLogs($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_workspace_analytics")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAnalytics(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceAnalytics($event->getWorkspace()));
    }

    /**
     * @DI\Observe("open_tool_desktop_parameters")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopParameters(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopParameters());
    }

    /**
     * Renders the workspace properties page.
     *
     * @param int $workspaceId
     *
     * @return string
     */
    public function workspaceParameters($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $tools = $this->toolManager->getToolByCriterias(
            ['isConfigurableInWorkspace' => true, 'isDisplayableInWorkspace' => true]
        );

        $canOpenResRights = true;

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:parameters.html.twig',
            ['workspace' => $workspace, 'tools' => $tools, 'canOpenResRights' => $canOpenResRights]
        );
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopParameters()
    {
        $desktopTools = $this->toolManager->getToolByCriterias(
            ['isConfigurableInDesktop' => true, 'isDisplayableInDesktop' => true]
        );
        $tools = [];

        foreach ($desktopTools as $desktopTool) {
            $toolName = $desktopTool->getName();

            if ('home' !== $toolName && 'parameters' !== $toolName) {
                $tools[] = $desktopTool;
            }
        }

        if (count($tools) > 1) {
            return $this->templating->render(
                'ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig',
                ['tools' => $tools]
            );
        }

        //otherwise only parameters exists so we return the parameters page.
        $params['_controller'] = 'ClarolineCoreBundle:Tool\DesktopParameters:desktopParametersMenu';

        $subRequest = $this->container->get('request')->duplicate(
            [],
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    public function workspaceLogs($workspaceId)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace */
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function workspaceAnalytics($workspace)
    {
        $params = [
            '_controller' => 'ClarolineCoreBundle:WorkspaceAnalytics:showTraffic',
            'workspaceId' => $workspace->getId(),
        ];

        $subRequest = $this->container->get('request')->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    /**
     * @DI\Observe("open_tool_workspace_users")
     *
     * @param DisplayToolEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onDisplay(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate(
            [], null,
            [
                '_controller' => 'ClarolineCoreBundle:Workspace:usersManagement',
                'workspace' => $event->getWorkspace(),
            ]
        );

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureDesktopTool(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $tool = $event->getTool();

        if ('anon.' !== $user && !is_null($tool)) {
            $toolName = $tool->getName();
            $translatedName = $this->translator->trans($toolName, [], 'tools');
            $route = $this->router->generate(
                'claro_desktop_open_tool',
                ['toolName' => $toolName]
            );

            $menu = $event->getMenu();
            $menu->addChild(
                $translatedName,
                ['uri' => $route]
            )->setExtra('icon', 'fa fa-'.$tool->getClass())
            ->setExtra('title', $translatedName);

            return $menu;
        }
    }

    /**
     * @DI\Observe("claroline_top_bar_right_menu_configure_desktop_tool")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarRightMenuConfigureDesktopTool(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $tool = $event->getTool();

        if ('anon.' !== $user && !is_null($tool)) {
            $toolName = $tool->getName();
            $translatedName = $this->translator->trans($toolName, [], 'tools');
            $menu = $event->getMenu();
            $menu->addChild(
                $translatedName,
                [
                    'route' => 'claro_desktop_open_tool',
                    'routeParameters' => ['toolName' => $toolName],
                ]
            )->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-'.$tool->getClass());

            return $menu;
        }
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool_parameters")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureParameters(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $tool = $event->getTool();

        if ('anon.' !== $user) {
            $parametersTitle = $this->translator->trans(
                'preferences',
                [],
                'platform'
            );
            $menu = $event->getMenu();
            $menu->addChild(
                $this->translator->trans('preferences', [], 'platform'),
                ['route' => 'claro_desktop_parameters_menu']
            )->setExtra('icon', 'fa fa-'.$tool->getClass())
            ->setExtra('title', $parametersTitle);

            return $menu;
        }
    }
}
