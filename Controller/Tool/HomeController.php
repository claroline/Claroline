<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the workspace/desktop home page.
 */
class HomeController extends Controller
{
    private $em;
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $roleManager;
    private $securityContext;
    private $toolManager;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "widgetManager"      = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        RoleManager $roleManager,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        WidgetManager $widgetManager
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->homeTabManager = $homeTabManager;
        $this->request = $request;
        $this->roleManager = $roleManager;
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget",
     *     name="claro_workspace_widget_properties"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:widgetProperties.html.twig")
     *
     * Renders the workspace widget properties page.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceWidgetsPropertiesAction(AbstractWorkspace $workspace)
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $configs = $this->widgetManager->getWorkspaceInstances($workspace);
        $widgets = $this->widgetManager->getWorkspaceWidgets();

        return array(
            'workspace' => $workspace,
            'configs' => $configs,
            'tool' => $this->getHomeTool(),
            'widgets' => $widgets
        );
    }

    /**
     * @EXT\Route(
     *     "/widget/workspace/config/{widgetInstanceId}",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param WidgetInstance $widgetInstance
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction(WidgetInstance $widgetInstance)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return array(
            'workspace' => $widgetInstance->getWorkspace(),
            'content' => $event->getContent(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "/widget/form/{widgetInstance}",
     *     name="claro_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")

     * @EXT\Template("ClarolineCoreBundle:Widget:config_simple_text_form.html.twig")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return Response
     */
    public function getWidgetFormConfigurationAction(WidgetInstance $widgetInstance)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/widget/content/{widgetInstanceId}",
     *     name="claro_widget_content",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     *
     * Asks a widget to render its content.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return Response
     */
    public function getWidgetContentAction(WidgetInstance $widgetInstance)
    {
        $event = $this->eventDispatcher->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}",
            'DisplayWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/properties",
     *     name="claro_desktop_widget_properties"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:widgetProperties.html.twig")
     *
     * Displays the widget configuration page.
     */
    public function desktopWidgetPropertiesAction()
    {
        $user = $this->securityContext->getToken()->getUser();
        $widgetInstances = $this->widgetManager->getDesktopInstances($user);
        $widgets = $this->widgetManager->getDesktopWidgets();

        return array(
            'widgetInstances' => $widgetInstances,
            'user' => $user,
            'tool' => $this->getHomeTool(),
            'widgets' => $widgets
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/instance/create/form",
     *     name="claro_desktop_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetInstanceCreateForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @return Response
     */
    public function desktopWidgetInstanceCreateFormAction()
    {
        $this->checkUserAccess();

        $widgetInstance = new WidgetInstance();
        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array(),
            $widgetInstance
        );

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/instance/create",
     *     name="claro_desktop_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetInstanceCreateForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @return Response
     */
    public function desktopWidgetInstanceCreateAction()
    {
        $this->checkUserAccess();

        $user = $this->securityContext->getToken()->getUser();
        $widgetInstance = new WidgetInstance();

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array(),
            $widgetInstance
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $widgetInstance->setUser($user);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(true);

            $this->widgetManager->insertWidgetInstance($widgetInstance);

            return new Response($widgetInstance->getId(), 201);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/widget/instance/create/form",
     *     name="claro_workspace_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetInstanceCreateForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @return Response
     */
    public function workspaceWidgetInstanceCreateFormAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $widgetInstance = new WidgetInstance();
        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array(),
            $widgetInstance
        );

        return array(
            'workspace' => $workspace,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/widget/instance/create",
     *     name="claro_workspace_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetInstanceCreateForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @return Response
     */
    public function workspaceWidgetInstanceCreateAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $widgetInstance = new WidgetInstance();

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array(),
            $widgetInstance
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $widgetInstance->setWorkspace($workspace);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(false);

            $this->widgetManager->insertWidgetInstance($widgetInstance);

            return new Response($widgetInstance->getId(), 201);
        }

        return array(
            'workspace' => $workspace,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/{widget}/create",
     *     name="claro_desktop_widget_create",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:desktopWidgetConfigRow.html.twig")
     */
    public function createDesktopWidgetInstance(Widget $widget)
    {
        $instance = $this->widgetManager->createInstance(
            $widget,
            false,
            true,
            $this->securityContext->getToken()->getUser()
        );

        return array('config' => $instance);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/widget/{widget}/create",
     *     name="claro_workspace_widget_create",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:workspaceWidgetConfigRow.html.twig")
     */
    public function createWorkspaceWidgetInstance(Widget $widget, AbstractWorkspace $workspace)
    {
        $instance = $this->widgetManager->createInstance($widget, false, false, null, $workspace);

        return array('config' => $instance);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/remove/{widgetInstance}",
     *     name = "claro_desktop_remove_widget",
     *     options={"expose"=true}
     * )
     */
    public function removeDesktopWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetManager->removeInstance($widgetInstance);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/widget/remove/{widgetInstance}",
     *     name = "claro_workspace_remove_widget",
     *     options={"expose"=true}
     * )
     */
    public function removeWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetManager->removeInstance($widgetInstance);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/widget/desktop/config/{widgetInstanceId}",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param WidgetInstance $widgetInstance
     *
     * @return Response
     */
    public function dekstopConfigureWidgetAction(WidgetInstance $widgetInstance)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return array('content' => $event->getContent(), 'tool' => $this->getHomeTool());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/properties",
     *     name="claro_desktop_home_tab_properties",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:homeTabProperties.html.twig")
     *
     * Displays the homeTab configuration page.
     *
     * @return Response
     */
    public function desktopHomeTabPropertiesAction()
    {
        $this->checkUserAccess();

        $user = $this->securityContext->getToken()->getUser();
        $adminHomeTabConfigs = $this->homeTabManager->generateAdminHomeTabConfigsByUser($user);
        $homeTabConfigs = $this->homeTabManager->getDesktopHomeTabConfigsByUser($user);

        $nbWidgets = array();

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {
            $adminWidgetConfigs = $this->homeTabManager
                ->getVisibleAdminWidgetConfigs($adminHomeTabConfig->getHomeTab());
            $userWidgetConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByUser($adminHomeTabConfig->getHomeTab(), $user);
            $nbWidgets[$adminHomeTabConfig->getId()] =
                count($adminWidgetConfigs) + count($userWidgetConfigs);
        }
        foreach ($homeTabConfigs as $homeTabConfig) {
            $widgetConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByUser($homeTabConfig->getHomeTab(), $user);
            $nbWidgets[$homeTabConfig->getId()] = count($widgetConfigs);
        }

        return array(
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'homeTabConfigs' => $homeTabConfigs,
            'tool' => $this->getHomeTool(),
            'nbWidgets' => $nbWidgets
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create/form",
     *     name="claro_desktop_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabCreateForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function desktopHomeTabCreateFormAction()
    {
        $this->checkUserAccess();

        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create",
     *     name="claro_desktop_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabCreateForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function desktopHomeTabCreateAction()
    {
        $this->checkUserAccess();

        $user = $this->securityContext->getToken()->getUser();
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $homeTab->setType('desktop');
            $homeTab->setUser($user);
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('desktop');
            $homeTabConfig->setUser($user);
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);

            $lastOrder = $this->homeTabManager->getOrderOfLastDesktopHomeTabConfigByUser($user);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return new Response('success', 201);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabId}/edit/form",
     *     name="claro_desktop_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabEditForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function desktopHomeTabEditFormAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabId}/edit",
     *     name="claro_desktop_home_tab_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabEditForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function desktopHomeTabEditAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabId}/{tabOrder}/delete",
     *     name="claro_desktop_home_tab_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     *
     * Delete the given homeTab.
     *
     * @return Response
     */
    public function desktopHomeTabDeleteAction(HomeTab $homeTab, $tabOrder)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $this->homeTabManager->deleteHomeTab($homeTab, 'desktop', $tabOrder);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/tab/{tabId}/{withConfig}",
     *     name="claro_display_desktop_home_tabs",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabs.html.twig")
     *
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayDesktopHomeTabsAction($tabId, $withConfig = 0)
    {
        $user = $this->securityContext->getToken()->getUser();
        $adminHomeTabConfigs = $this->homeTabManager
            ->generateAdminHomeTabConfigsByUser($user);
        $userHomeTabConfigs = $this->homeTabManager
            ->getDesktopHomeTabConfigsByUser($user);
        $homeTabId = $tabId;

        if ($homeTabId == -1) {
            foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {
                if ($adminHomeTabConfig->isVisible() || ($withConfig === 1)) {
                    $homeTabId = $adminHomeTabConfig->getHomeTab()->getId();
                    break;
                }
            }
        }
        if ($homeTabId == -1) {
            foreach ($userHomeTabConfigs as $userHomeTabConfig) {
                if ($userHomeTabConfig->isVisible() || ($withConfig === 1)) {
                    $homeTabId = $userHomeTabConfig->getHomeTab()->getId();
                    break;
                }
            }
        }
        if (($withConfig == 1) && ($homeTabId == 0)) {
            $userHomeTabConfig = end($userHomeTabConfigs);

            if ($userHomeTabConfig !== false) {
                $homeTabId = $userHomeTabConfig->getHomeTab()->getId();
            }
        }

        return array(
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'userHomeTabConfigs' => $userHomeTabConfigs,
            'tabId' => $homeTabId,
            'withConfig' => $withConfig
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/home_tab/properties",
     *     name="claro_workspace_home_tab_properties",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:homeTabProperties.html.twig")
     *
     * Displays the homeTab configuration page.
     *
     * @return Response
     */
    public function workspaceHomeTabPropertiesAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $homeTabConfigs = $this->homeTabManager
            ->getWorkspaceHomeTabConfigsByWorkspace($workspace);

        $nbWidgets = array();

        foreach ($homeTabConfigs as $homeTabConfig) {
            $widgetConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByWorkspace(
                    $homeTabConfig->getHomeTab(),
                    $workspace
                );
            $nbWidgets[$homeTabConfig->getId()] = count($widgetConfigs);
        }

        return array(
            'workspace' => $workspace,
            'homeTabConfigs' => $homeTabConfigs,
            'tool' => $this->getHomeTool(),
            'nbWidgets' => $nbWidgets
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/create/form",
     *     name="claro_workspace_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabCreateForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function workspaceHomeTabCreateFormAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $homeTab = new HomeTab();
        $form = $this->formFactory
            ->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'workspace' => $workspace,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/home_tab/create",
     *     name="claro_workspace_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabCreateForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabCreateAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $homeTab = new HomeTab();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $homeTab->setType('workspace');
            $homeTab->setWorkspace($workspace);
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('workspace');
            $homeTabConfig->setWorkspace($workspace);
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);

            $lastOrder = $this->homeTabManager
                ->getOrderOfLastWorkspaceHomeTabConfigByWorkspace($workspace);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return new Response('success', 201);
        }

        return array(
            'workspace' => $workspace,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/home_tab/{homeTabId}/edit/form",
     *     name="claro_workspace_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabEditForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function workspaceHomeTabEditFormAction(
        AbstractWorkspace $workspace,
        HomeTab $homeTab
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'homeTab' => $homeTab
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/home_tab/{homeTabId}/edit/form",
     *     name="claro_workspace_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabEditForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabEditAction(
        AbstractWorkspace $workspace,
        HomeTab $homeTab
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return new Response('success', 204);
        }

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'homeTab' => $homeTab
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}home_tab/{homeTabId}/{tabOrder}/delete",
     *     name="claro_workspace_home_tab_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     *
     * Delete the given homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabDeleteAction(
        AbstractWorkspace $workspace,
        HomeTab $homeTab,
        $tabOrder
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $this->homeTabManager->deleteHomeTab($homeTab, 'workspace', $tabOrder);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/visibility/{visible}/update",
     *     name="claro_home_tab_update_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Configure visibility of an Home tab
     *
     * @return Response
     */
    public function homeTabUpdateVisibilityAction(HomeTabConfig $homeTabConfig, $visible)
    {
        $workspace = $homeTabConfig->getWorkspace();
        $homeTab = $homeTabConfig->getHomeTab();

        if (!is_null($workspace)) {
            $this->checkWorkspaceAccess($workspace);
            $this->checkWorkspaceAccessForAdminHomeTab($homeTab, $workspace);
        }
        else {
            $user = $this->securityContext->getToken()->getUser();
            $this->checkUserAccess();
            $this->checkUserAccessForAdminHomeTab($homeTab, $user);
        }

        $isVisible = ($visible === 'visible') ? true : false;
        $this->homeTabManager->updateVisibility($homeTabConfig, $isVisible);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/home_tab/{homeTabId}/widgets/configuration",
     *     name="claro_desktop_home_tab_widgets_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabWidgetsConfig.html.twig")
     *
     * Displays the widgets configuration page for given Home tab.
     *
     * @return Response
     */
    public function desktopHomeTabWidgetsConfigAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $widgetConfigs = $this->homeTabManager->getWidgetConfigsByUser($homeTab, $user);
        $lastWidgetOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInHomeTabByUser($homeTab, $user);
        $lastOrder = is_null($lastWidgetOrder) ? 1 : $lastWidgetOrder['order_max'];

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'widgetConfigs' => $widgetConfigs,
            'lastWidgetOrder' => $lastOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/home_tab/{homeTabId}/widgets/configuration",
     *     name="claro_workspace_home_tab_widgets_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabWidgetsConfig.html.twig")
     *
     * Displays the widgets configuration page for given Home tab.
     *
     * @return Response
     */
    public function workspaceHomeTabWidgetsConfigAction(
        HomeTab $homeTab,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForAdminHomeTab($homeTab, $workspace);

        $widgetConfigs = $this->homeTabManager
            ->getWidgetConfigsByWorkspace($homeTab, $workspace);
        $lastWidgetOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInHomeTabByWorkspace($homeTab, $workspace);
        $lastOrder = is_null($lastWidgetOrder) ? 1 : $lastWidgetOrder['order_max'];

        return array(
            'tool' => $this->getHomeTool(),
            'workspace' => $workspace,
            'homeTab' => $homeTab,
            'widgetConfigs' => $widgetConfigs,
            'lastWidgetOrder' => $lastOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/admin/home_tab/{homeTabId}/widgets/configuration",
     *     name="claro_desktop_admin_home_tab_widgets_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopAdminHomeTabWidgetsConfig.html.twig")
     *
     * Displays the widgets configuration page for given Home tab.
     *
     * @return Response
     */
    public function desktopAdminHomeTabWidgetsConfigAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForAdminHomeTab($homeTab, $user);

        $adminWidgetConfigs = $this->homeTabManager
            ->getAdminWidgetConfigs($homeTab);

        $adminWConfigs = array();

        foreach ($adminWidgetConfigs as $adminWidgetConfig) {

            if ($adminWidgetConfig->isLocked()) {
                $adminWConfigs[] = $adminWidgetConfig;
            }
            else {
                $existingWidgetConfig = $this->homeTabManager
                    ->getUserAdminWidgetHomeTabConfig(
                        $homeTab,
                        $adminWidgetConfig->getWidgetInstance(),
                        $user
                    );
                if (count($existingWidgetConfig) === 0) {
                    $newWHTC = new WidgetHomeTabConfig();
                    $newWHTC->setHomeTab($homeTab);
                    $newWHTC->setWidgetInstance($adminWidgetConfig->getWidgetInstance());
                    $newWHTC->setUser($user);
                    $newWHTC->setWidgetOrder($adminWidgetConfig->getWidgetOrder());
                    $newWHTC->setVisible($adminWidgetConfig->isVisible());
                    $newWHTC->setLocked(false);
                    $newWHTC->setType('admin_desktop');
                    $this->homeTabManager->insertWidgetHomeTabConfig($newWHTC);
                    $adminWConfigs[] = $newWHTC;
                }
                else {
                    $adminWConfigs[] = $existingWidgetConfig[0];
                }
            }
        }

        $widgetConfigs = $this->homeTabManager
            ->getWidgetConfigsByUser($homeTab, $user);

        $nbWidgetConfigs = count($widgetConfigs);

        $lastOrder = ($nbWidgetConfigs === 0) ? 1 : $nbWidgetConfigs;

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'adminWidgetConfigs' => $adminWConfigs,
            'widgetConfigs' => $widgetConfigs,
            'lastWidgetOrder' => $lastOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/home_tab/{homeTabId}/widgets/available/list",
     *     name="claro_desktop_home_tab_addable_widgets_list",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:listDesktopAddableWidgets.html.twig")
     *
     * Displays the list of widgets that can be added to the given Home tab.
     *
     * @return Response
     */
    public function listDesktopAddableWidgetsAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $widgetConfigs = $this->homeTabManager
            ->getWidgetConfigsByUser($homeTab, $user);

        $excludedWidgetInstanceList = array();

        foreach ($widgetConfigs as $widgetConfig) {
            $excludedWidgetInstanceList[] = $widgetConfig->getWidgetInstance()->getId();
        }
        $adminWidgetInstances = $this->widgetManager
            ->getAdminDesktopWidgetInstance($excludedWidgetInstanceList);
        $widgetInstances = $this->widgetManager
            ->getDesktopWidgetInstance($user, $excludedWidgetInstanceList);

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'adminWidgetInstances' => $adminWidgetInstances,
            'widgetInstances' => $widgetInstances
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/admin/home_tab/{homeTabId}/widgets/available/list",
     *     name="claro_desktop_admin_home_tab_addable_widgets_list",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:listDesktopAdminAddableWidgets.html.twig")
     *
     * Displays the list of widgets that can be added to the given Home tab.
     *
     * @return Response
     */
    public function listDesktopAdminAddableWidgetsAction(HomeTab $homeTab)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForAdminHomeTab($homeTab, $user);

        $adminWidgetConfigs = $this->homeTabManager
            ->getAdminWidgetConfigs($homeTab);
        $widgetConfigs = $this->homeTabManager
            ->getWidgetConfigsByUser($homeTab, $user);

        $currentAdminWidgetInstanceList = array();
        $currentWidgetInstanceList = array();

        foreach ($adminWidgetConfigs as $adminWidgetConfig) {
            $currentAdminWidgetInstanceList[] = $adminWidgetConfig->getWidgetInstance()->getId();
        }

        foreach ($widgetConfigs as $widgetConfig) {
            $currentWidgetInstanceList[] = $widgetConfig->getWidgetInstance()->getId();
        }
        $excludedWidgetInstanceList = array_merge(
            $currentAdminWidgetInstanceList,
            $currentWidgetInstanceList
        );
        $adminWidgetInstances = $this->widgetManager
            ->getAdminDesktopWidgetInstance($excludedWidgetInstanceList);
        $widgetInstances = $this->widgetManager
            ->getDesktopWidgetInstance($user, $excludedWidgetInstanceList);

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'adminWidgetInstances' => $adminWidgetInstances,
            'widgetInstances' => $widgetInstances
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/home_tab/{homeTabId}/widgets/available/list",
     *     name="claro_workspace_home_tab_addable_widgets_list",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:listWorkspaceAddableWidgets.html.twig")
     *
     * Displays the list of widgets that can be added to the given Home tab.
     *
     * @return Response
     */
    public function listWorkspaceAddableWidgetsAction(
        HomeTab $homeTab,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForAdminHomeTab($homeTab, $workspace);

        $widgetConfigs = $this->homeTabManager
            ->getWidgetConfigsByWorkspace($homeTab, $workspace);
        $excludedWidgetInstanceList = array();

        foreach ($widgetConfigs as $widgetConfig) {
            $excludedWidgetInstanceList[] = $widgetConfig->getWidgetInstance()->getId();
        }
        $adminWidgetInstances = $this->widgetManager
            ->getAdminWorkspaceWidgetInstance($excludedWidgetInstanceList);
        $widgetInstances = $this->widgetManager
            ->getWorkspaceWidgetInstance($workspace, $excludedWidgetInstanceList);

        return array(
            'tool' => $this->getHomeTool(),
            'workspace' => $workspace,
            'homeTab' => $homeTab,
            'adminWidgetInstances' => $adminWidgetInstances,
            'widgetInstances' => $widgetInstances
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/home_tab/{homeTabId}/associate/widget/{widgetInstanceId}",
     *     name="claro_desktop_associate_widget_to_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     *
     * Associate given WidgetInstance to given Home tab.
     *
     * @return Response
     */
    public function associateDesktopWidgetToHomeTabAction(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();

        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
        $widgetHomeTabConfig->setUser($user);
        $widgetHomeTabConfig->setVisible(true);
        $widgetHomeTabConfig->setLocked(false);
        $widgetHomeTabConfig->setType('desktop');

        $lastOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInHomeTabByUser($homeTab, $user);

        if (is_null($lastOrder['order_max'])) {
            $widgetHomeTabConfig->setWidgetOrder(1);
        }
        else {
            $widgetHomeTabConfig->setWidgetOrder($lastOrder['order_max'] + 1);
        }

        $this->homeTabManager->insertWidgetHomeTabConfig($widgetHomeTabConfig);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/home_tab/{homeTabId}/associate/widget/{widgetInstanceId}",
     *     name="claro_workspace_associate_widget_to_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Associate given WidgetInstance to given Home tab.
     *
     * @return Response
     */
    public function associateWorkspaceWidgetToHomeTabAction(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);

        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
        $widgetHomeTabConfig->setWorkspace($workspace);
        $widgetHomeTabConfig->setVisible(true);
        $widgetHomeTabConfig->setLocked(false);
        $widgetHomeTabConfig->setType('workspace');

        $lastOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInHomeTabByWorkspace($homeTab, $workspace);

        if (is_null($lastOrder['order_max'])) {
            $widgetHomeTabConfig->setWidgetOrder(1);
        }
        else {
            $widgetHomeTabConfig->setWidgetOrder($lastOrder['order_max'] + 1);
        }

        $this->homeTabManager->insertWidgetHomeTabConfig($widgetHomeTabConfig);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget_home_tab_config/{widgetHomeTabConfigId}/change/visibility",
     *     name="claro_desktop_widget_home_tab_config_change_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     *
     * Change visibility of the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function desktopWidgetHomeTabConfigChangeVisibilityAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);

        $this->homeTabManager->changeVisibilityWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/widget_home_tab_config/{widgetHomeTabConfigId}/change/visibility",
     *     name="claro_workspace_widget_home_tab_config_change_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Change visibility of the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function workspaceWidgetHomeTabConfigChangeVisibilityAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $workspace
        );

        $this->homeTabManager->changeVisibilityWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget_home_tab_config/{widgetHomeTabConfigId}/delete",
     *     name="claro_desktop_widget_home_tab_config_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     *
     * Delete the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function desktopWidgetHomeTabConfigDeleteAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

        $this->homeTabManager->deleteWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        if ($this->hasUserAccessToWidgetInstance($widgetInstance, $user)) {
            $this->widgetManager->removeInstance($widgetInstance);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/widget_home_tab_config/{widgetHomeTabConfigId}/delete",
     *     name="claro_workspace_widget_home_tab_config_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Delete the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function workspaceWidgetHomeTabConfigDeleteAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $workspace
        );
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

        $this->homeTabManager->deleteWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        if ($this->hasWorkspaceAccessToWidgetInstance($widgetInstance, $workspace)) {
            $this->widgetManager->removeInstance($widgetInstance);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget_home_tab_config/{widgetHomeTabConfigId}/change/order/{direction}",
     *     name="claro_desktop_widget_home_tab_config_change_order",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     *
     * Change order of the given widgetHomeTabConfig in the given direction.
     *
     * @return Response
     */
    public function desktopWidgetHomeTabConfigChangeOrderAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        $direction
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);

        $status = $this->homeTabManager->changeOrderWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $direction
        );

        return new Response($status, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/widget_home_tab_config/{widgetHomeTabConfigId}/change/order/{direction}",
     *     name="claro_workspace_widget_home_tab_config_change_order",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Change order of the given widgetHomeTabConfig in the given direction.
     *
     * @return Response
     */
    public function workspaceWidgetHomeTabConfigChangeOrderAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        AbstractWorkspace $workspace,
        $direction
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $workspace
        );

        $status = $this->homeTabManager->changeOrderWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $direction
        );

        return new Response($status, 200);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/{widgetInstanceId}/name/edit/form",
     *     name = "claro_desktop_widget_name_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetNameEditForm.html.twig")
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function editDesktopWidgetNameFormAction(WidgetInstance $widgetInstance)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForWidgetInstance($widgetInstance, $user);

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );

        return array(
            'form' => $form->createView(),
            'widgetInstance' => $widgetInstance
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/{widgetInstanceId}/name/edit",
     *     name = "claro_desktop_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetNameEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return array
     */
    public function editDesktopWidgetNameAction(
        WidgetInstance $widgetInstance,
        User $user
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForWidgetInstance($widgetInstance, $user);

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->widgetManager->insertWidgetInstance($widgetInstance);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'widgetInstance' => $widgetInstance
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/widget/{widgetInstanceId}/name/edit/form",
     *     name = "claro_workspace_widget_name_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetNameEditForm.html.twig")
     *
     * @return array
     */
    public function editWorkspaceWidgetNameFormAction(
        WidgetInstance $widgetInstance,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForWidgetInstance($widgetInstance, $workspace);

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );

        return array(
            'form' => $form->createView(),
            'widgetInstance' => $widgetInstance,
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/widget/{widgetInstanceId}/name/edit",
     *     name = "claro_workspace_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetNameEditForm.html.twig")
     *
     * @return array
     */
    public function editWorkspaceWidgetNameAction(
        WidgetInstance $widgetInstance,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $this->checkWorkspaceAccessForWidgetInstance($widgetInstance, $workspace);

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->widgetManager->insertWidgetInstance($widgetInstance);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'widgetInstance' => $widgetInstance,
            'workspace' => $workspace
        );
    }

    private function checkUserAccess()
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccess(AbstractWorkspace $workspace)
    {
        $role = $this->roleManager->getManagerRole($workspace);

        if (is_null($role) || !$this->securityContext->isGranted($role->getName())) {
            throw new AccessDeniedException();
        }
    }

    private function checkUserAccessForHomeTab(HomeTab $homeTab, User $user)
    {
        $homeTabUser = $homeTab->getUser();

        if (is_null($homeTabUser) || ($homeTabUser->getId() !== $user->getId())) {
            throw new AccessDeniedException();
        }
    }

    private function checkUserAccessForAdminHomeTab(HomeTab $homeTab, User $user)
    {
        $isAdminUser = false;
        $isUser = false;

        if ($homeTab->getType() === 'admin_desktop') {
            $homeTabConfig = $this->homeTabManager
                ->getAdminDesktopHomeTabConfigByHomeTab($homeTab);
            $isAdminUser = !(is_null($homeTabConfig) || $homeTabConfig->isLocked());
        }
        else {
            $homeTabConfig = $this->homeTabManager
                ->getHomeTabConfigByHomeTabAndUser($homeTab, $user);
            $homeTabUser = is_null($homeTabConfig) ? null : $homeTabConfig->getUser();
            $isUser = !is_null($homeTabUser) && $homeTabUser->getId() === $user->getId();
        }

        if (!($isAdminUser || $isUser)) {

            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForHomeTab(
        HomeTab $homeTab,
        AbstractWorkspace $workspace
    )
    {
        $homeTabWorkspace = $homeTab->getWorkspace();

        if (is_null($homeTabWorkspace) || ($homeTabWorkspace->getId() !== $workspace->getId())) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForAdminHomeTab(
        HomeTab $homeTab,
        AbstractWorkspace $workspace
    )
    {
        $homeTabWorkspace = $homeTab->getWorkspace();

        $isAdminWorkspace =
            is_null($homeTabWorkspace) && ($homeTab->getType() === 'admin_workspace');
        $isWorkspace = !is_null($homeTabWorkspace) &&
            ($homeTabWorkspace->getId() === $workspace->getId());

        if (!($isAdminWorkspace || $isWorkspace)) {
            throw new AccessDeniedException();
        }
    }

    private function checkUserAccessForWidgetHomeTabConfig(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        User $user
    )
    {
        $widgetHomeTabConfigUser = $widgetHomeTabConfig->getUser();

        if (($widgetHomeTabConfig->getType() !== 'desktop'
            && $widgetHomeTabConfig->getType() !== 'admin_desktop') ||
            is_null($widgetHomeTabConfigUser) ||
            ($widgetHomeTabConfigUser->getId() !== $user->getId())) {

            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForWidgetHomeTabConfig(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        AbstractWorkspace $workspace
    )
    {
        $widgetHomeTabConfigWorkspace = $widgetHomeTabConfig->getWorkspace();

        if ($widgetHomeTabConfig->getType() !== 'workspace' ||
            is_null($widgetHomeTabConfigWorkspace) ||
            ($widgetHomeTabConfigWorkspace->getId() !== $workspace->getId())) {

            throw new AccessDeniedException();
        }
    }

    private function checkUserAccessForWidgetInstance(
        WidgetInstance $widgetInstance,
        User $user
    )
    {
        $widgetUser = $widgetInstance->getUser();

        if (is_null($widgetUser) || ($widgetUser->getId() !== $user->getId())) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForWidgetInstance(
        WidgetInstance $widgetInstance,
        AbstractWorkspace $workspace
    )
    {
        $widgetWorkspace = $widgetInstance->getWorkspace();

        if (is_null($widgetWorkspace) || ($widgetWorkspace->getId() !== $workspace->getId())) {
            throw new AccessDeniedException();
        }
    }

    private function hasUserAccessToWidgetInstance(
        WidgetInstance $widgetInstance,
        User $user
    )
    {
        $widgetUser = $widgetInstance->getUser();

        if (is_null($widgetUser) || ($widgetUser->getId() !== $user->getId())) {

            return false;
        }

        return true;
    }

    private function hasWorkspaceAccessToWidgetInstance(
        WidgetInstance $widgetInstance,
        AbstractWorkspace $workspace
    )
    {
        $widgetWorkspace = $widgetInstance->getWorkspace();

        if (is_null($widgetWorkspace) || ($widgetWorkspace->getId() !== $workspace->getId())) {

            return false;
        }

        return true;
    }

    private function getHomeTool()
    {
        return $this->toolManager->getOneToolByName('home');
    }
}
