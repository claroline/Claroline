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
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\MessageManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class ToolListener
{
    private $container;
    private $formFactory;
    private $httpKernel;
    private $messageManager;
    private $rightsManager;
    private $router;
    private $securityContext;
    private $templating;
    private $toolManager;
    private $translator;
    private $workspaceManager;
    const R_U = "ROLE_USER";
    const R_A = "ROLE_ADMIN";

    /**
     * @DI\InjectParams({
     *     "container"        = @DI\Inject("service_container"),
     *     "formFactory"      = @DI\Inject("claroline.form.factory"),
     *     "httpKernel"       = @DI\Inject("http_kernel"),
     *     "messageManager"   = @DI\Inject("claroline.manager.message_manager"),
     *     "rightsManager"    = @DI\Inject("claroline.manager.rights_manager"),
     *     "router"           = @DI\Inject("router"),
     *     "securityContext"  = @DI\Inject("security.context"),
     *     "templating"       = @DI\Inject("templating"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        FormFactory $formFactory,
        $httpKernel,
        MessageManager $messageManager,
        RightsManager $rightsManager,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        $templating,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    )
    {
        $this->container = $container;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->messageManager = $messageManager;
        $this->rightsManager = $rightsManager;
        $this->router = $router;
        $this->securityContext = $securityContext;
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
     * @DI\Observe("open_tool_workspace_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceAgenda($event->getWorkspace()));
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
     * @DI\Observe("open_tool_desktop_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopAgenda());
    }

    /**
     * Renders the workspace properties page.
     *
     * @param integer $workspaceId
     *
     * @return string
     */
    public function workspaceParameters($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $tools = $this->toolManager->getToolByCriterias(
            array('isConfigurableInWorkspace' => true, 'isDisplayableInWorkspace' => true)
        );

        $canOpenResRights = true;

        if ($workspace->isPersonal() && !$this->rightsManager->canEditPwsPerm(
            $this->container->get('security.context')->getToken()
        )) {
            $canOpenResRights = false;
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:parameters.html.twig',
            array('workspace' => $workspace, 'tools' => $tools, 'canOpenResRights' => $canOpenResRights)
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
            array('isConfigurableInDesktop' => true, 'isDisplayableInDesktop' => true)
        );
        $tools = array();

        foreach ($desktopTools as $desktopTool) {
            $toolName = $desktopTool->getName();

            if ($toolName !== 'home' && $toolName !== 'parameters') {
                $tools[] = $desktopTool;
            }
        }

        if (count($tools) > 1) {
            return $this->templating->render(
                'ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig',
                array('tools' => $tools)
            );
        }

        //otherwise only parameters exists so we return the parameters page.
        $params['_controller'] = 'ClarolineCoreBundle:Tool\DesktopParameters:desktopParametersMenu';

        $subRequest = $this->container->get('request')->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    public function workspaceAgenda(Workspace $workspace)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')
            ->findByWorkspaceId($workspace->getId(), true);
        $canCreate = $this->container->get('security.context')
            ->isGranted(array('agenda', 'edit'), $workspace);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda.html.twig',
            array(
                'workspace' => $workspace,
                'canCreate' => $canCreate
            )
        );
    }

    public function workspaceLogs($workspaceId)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace */
        $workspace  = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function workspaceAnalytics($workspace)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:WorkspaceAnalytics:showTraffic',
            'workspaceId' => $workspace->getId()
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    public function desktopAgenda()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, true);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, false);
        $workspaces = array();
        $filters = array();

        foreach ($listEvents as $event) {
            $filters[$event->getWorkspace()->getId()] = $event->getWorkspace()->getName();
        }

        if (count($listEventsDesktop) > 0) {
            $filters[0] = $this->container->get('translator')->trans('desktop', array(), 'platform');
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/desktop/agenda:agenda.html.twig',
            array(
                'listEvents' => $listEventsDesktop,
                'filters' => $filters
            )
        );
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureDesktopTool(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();
        $tool = $event->getTool();

        if ($user !== 'anon.' && !is_null($tool)) {
            $toolName = $tool->getName();
            $translatedName = $tool->getDisplayedName();
            $route = $this->router->generate(
                'claro_desktop_open_tool',
                array('toolName' => $toolName)
            );

            $menu = $event->getMenu();
            $menu->addChild(
                $translatedName,
                array('uri' => $route)
            )->setExtra('icon', 'fa fa-' . $tool->getClass())
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
        $user = $this->securityContext->getToken()->getUser();
        $tool = $event->getTool();

        if ($user !== 'anon.' && !is_null($tool)) {
            $toolName = $tool->getName();
            $translatedName = $tool->getDisplayedName();
            $menu = $event->getMenu();
            $menu->addChild(
                $translatedName,
                array(
                    'route' => 'claro_desktop_open_tool',
                    'routeParameters' => array('toolName' => $toolName)
                )
            )->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-' . $tool->getClass());

            return $menu;
        }
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool_message")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureMessage(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();
        $tool = $event->getTool();

        if ($user !== 'anon.') {
            $countUnreadMessages = $this->messageManager->getNbUnreadMessages($user);
            $messageTitle = $this->translator->trans(
                'new_message_alert',
                array('%count%' => $countUnreadMessages),
                'platform'
            );
            $menu = $event->getMenu();
            $messageMenuLink = $menu->addChild(
                $this->translator->trans('messages', array(), 'cursus'),
                array('route' => 'claro_message_list_received')
            )->setExtra('icon', 'fa fa-' . $tool->getClass())
            ->setExtra('title', $messageTitle);

            if ($countUnreadMessages > 0) {
                $messageMenuLink->setExtra('badge', $countUnreadMessages);
            }

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
        $user = $this->securityContext->getToken()->getUser();
        $tool = $event->getTool();

        if ($user !== 'anon.') {
            $parametersTitle = $tool->getDisplayedName();
            $menu = $event->getMenu();
            $menu->addChild(
                $this->translator->trans('preferences', array(), 'platform'),
                array('route' => 'claro_desktop_parameters_menu')
            )->setExtra('icon', 'fa fa-' . $tool->getClass())
            ->setExtra('title', $parametersTitle);

            return $menu;
        }
    }
}
