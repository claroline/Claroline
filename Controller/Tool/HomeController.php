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
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
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
     *     "/widget/workspace/config/{config}",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig")
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget            $widget
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction(WidgetInstance $config)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$config->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($config)
        );

        return array('workspace' => $config->getWorkspace(), 'content' => $event->getContent(), 'tool' => $this->getHomeTool());
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
        $configs = $this->widgetManager->getDesktopInstances($user);
        $widgets = $this->widgetManager->getDesktopWidgets();

        return array(
            'configs' => $configs,
            'user' => $user,
            'tool' => $this->getHomeTool(),
            'widgets' => $widgets
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
        $instance = $this->widgetManager->createInstance($widget, false, true, $this->securityContext->getToken()->getUser());
        
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
        
        return new Response(204);
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
        
        return new Response(204);
    }

    /**
     * @EXT\Route(
     *     "/widget/desktop/config/{config}",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig")
     * 
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget            $widget
     *
     * @return Response
     */
    public function dekstopConfigureWidgetAction(WidgetInstance $config)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$config->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($config)
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
     *     "desktop/user/home_tab/create/form",
     *     name="claro_user_desktop_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabCreateForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function userDesktopHomeTabCreateFormAction()
    {
        $this->checkUserAccess();

        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/create",
     *     name="claro_user_desktop_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabCreateForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function userDesktopHomeTabCreateAction()
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
            $homeTabConfig->setVisible(false);

            $lastOrder = $this->homeTabManager->getOrderOfLastDesktopHomeTabConfigByUser($user);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return $this->redirect(
                $this->generateUrl('claro_desktop_home_tab_properties')
            );
        }

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabConfigId}/edit/form",
     *     name="claro_user_desktop_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabEditForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function userDesktopHomeTabEditFormAction(HomeTabConfig $homeTabConfig)
    {
        $this->checkUserAccess();
        $homeTab = $homeTabConfig->getHomeTab();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabConfigId}/{homeTabName}/edit",
     *     name="claro_user_desktop_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabEditForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function userDesktopHomeTabEditAction(HomeTabConfig $homeTabConfig, $homeTabName)
    {
        $this->checkUserAccess();
        $homeTab = $homeTabConfig->getHomeTab();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_desktop_home_tab_properties')
            );
        }

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabId}/{tabOrder}/delete",
     *     name="claro_user_desktop_home_tab_delete",
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
    public function userDesktopHomeTabDeleteAction(HomeTab $homeTab, $tabOrder)
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $this->homeTabManager->deleteHomeTab($homeTab, 'desktop', $tabOrder);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/tab/{tabId}",
     *     name="claro_display_desktop_home_tabs"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabs.html.twig")
     *
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayDesktopHomeTabsAction($tabId)
    {
        $user = $this->securityContext->getToken()->getUser();
        $adminHomeTabConfigsTemp = $this->homeTabManager
            ->generateAdminHomeTabConfigsByUser($user);
        $adminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigsTemp);
        $userHomeTabConfigs = $this->homeTabManager
            ->getVisibleDesktopHomeTabConfigsByUser($user);

        return array(
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'userHomeTabConfigs' => $userHomeTabConfigs,
            'tabId' => $tabId
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

        $adminHomeTabConfigs = $this->homeTabManager
            ->generateAdminHomeTabConfigsByWorkspace($workspace);
        $homeTabConfigs = $this->homeTabManager
            ->getWorkspaceHomeTabConfigsByWorkspace($workspace);

        $nbWidgets = array();

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {
            $workspaceWidgetConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByWorkspace(
                    $adminHomeTabConfig->getHomeTab(),
                    $workspace
                );
            $nbWidgets[$adminHomeTabConfig->getId()] = count($workspaceWidgetConfigs);
        }
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
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'homeTabConfigs' => $homeTabConfigs,
            'tool' => $this->getHomeTool(),
            'nbWidgets' => $nbWidgets
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/create/form",
     *     name="claro_user_workspace_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:userWorkspaceHomeTabCreateForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function userWorkspaceHomeTabCreateFormAction(AbstractWorkspace $workspace)
    {
        $this->checkWorkspaceAccess($workspace);

        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/create",
     *     name="claro_user_workspace_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:userWorkspaceHomeTabCreateForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function userWorkspaceHomeTabCreateAction(AbstractWorkspace $workspace)
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
            $homeTabConfig->setVisible(false);

            $lastOrder = $this->homeTabManager->getOrderOfLastWorkspaceHomeTabConfigByWorkspace($workspace);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_home_tab_properties',
                    array('workspaceId' => $workspace->getId())
                )
            );
        }

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/{homeTabConfigId}/edit/form",
     *     name="claro_user_workspace_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:userWorkspaceHomeTabEditForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function userWorkspaceHomeTabEditFormAction(
        AbstractWorkspace $workspace,
        HomeTabConfig $homeTabConfig
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/{homeTabConfigId}/{homeTabName}/edit/form",
     *     name="claro_user_workspace_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:userWorkspaceHomeTabEditForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function userWorkspaceHomeTabEditAction(
        AbstractWorkspace $workspace,
        HomeTabConfig $homeTabConfig,
        $homeTabName
    )
    {
        $this->checkWorkspaceAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_home_tab_properties',
                    array('workspaceId' => $workspace->getId())
                )
            );
        }

        return array(
            'workspace' => $workspace,
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspaceId}/user/home_tab/{homeTabId}/{tabOrder}/delete",
     *     name="claro_user_workspace_home_tab_delete",
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
    public function userWorkspaceHomeTabDeleteAction(
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
                        $adminWidgetConfig->getWidget(),
                        $user
                    );
                if (count($existingWidgetConfig) === 0) {
                    $newWHTC = new WidgetHomeTabConfig();
                    $newWHTC->setHomeTab($homeTab);
                    $newWHTC->setWidget($adminWidgetConfig->getWidget());
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
        $currentWidgetList = array();

        foreach ($widgetConfigs as $widgetConfig) {
            $currentWidgetList[] = $widgetConfig->getWidget()->getId();
        }
        $widgetDisplayConfigs = $this->homeTabManager
            ->getVisibleDesktopWidgetConfig($currentWidgetList);

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'widgetDisplayConfigs' => $widgetDisplayConfigs
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
        $currentWidgetList = array();

        foreach ($adminWidgetConfigs as $adminWidgetConfig) {
            $currentWidgetList[] = $adminWidgetConfig->getWidget()->getId();
        }

        foreach ($widgetConfigs as $widgetConfig) {
            $currentWidgetList[] = $widgetConfig->getWidget()->getId();
        }
        $widgetDisplayConfigs = $this->homeTabManager
            ->getVisibleDesktopWidgetConfig($currentWidgetList);

        return array(
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'widgetDisplayConfigs' => $widgetDisplayConfigs
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
        $currentWidgetList = array();

        foreach ($widgetConfigs as $widgetConfig) {
            $currentWidgetList[] = $widgetConfig->getWidget()->getId();
        }
        $widgetDisplayConfigs = $this->homeTabManager
            ->getVisibleWorkspaceWidgetConfig($currentWidgetList);

        return array(
            'tool' => $this->getHomeTool(),
            'workspace' => $workspace,
            'homeTab' => $homeTab,
            'widgetDisplayConfigs' => $widgetDisplayConfigs
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/home_tab/{homeTabId}/associate/widget/{widgetId}",
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
     *     "widget",
     *     class="ClarolineCoreBundle:Widget\Widget",
     *     options={"id" = "widgetId", "strictId" = true}
     * )
     *
     * Associate given Widget to given Home tab.
     *
     * @return Response
     */
    public function associateDesktopWidgetToHomeTabAction(
        HomeTab $homeTab,
        Widget $widget
    )
    {
        $this->checkUserAccess();
        $user = $this->securityContext->getToken()->getUser();

        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidget($widget);
        $widgetHomeTabConfig->setUser($user);
        $widgetHomeTabConfig->setVisible(false);
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
     *     "/workspace/{workspaceId}/home_tab/{homeTabId}/associate/widget/{widgetId}",
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
     *     "widget",
     *     class="ClarolineCoreBundle:Widget\Widget",
     *     options={"id" = "widgetId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Associate given Widget to given Home tab.
     *
     * @return Response
     */
    public function associateWorkspaceWidgetToHomeTabAction(
        HomeTab $homeTab,
        Widget $widget,
        AbstractWorkspace $workspace
    )
    {
        $this->checkWorkspaceAccess($workspace);

        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidget($widget);
        $widgetHomeTabConfig->setWorkspace($workspace);
        $widgetHomeTabConfig->setVisible(false);
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

        $this->homeTabManager->deleteWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

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

        $this->homeTabManager->deleteWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

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

        $this->homeTabManager->changeOrderWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $direction
        );

        return new Response('success', 204);
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

        $this->homeTabManager->changeOrderWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $direction
        );

        return new Response('success', 204);
    }
    
    /**
     * @EXT\Route(
     *     "/desktop/widget/name/form/{config}",
     *     name = "claro_desktop_widget_name_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:editWidgetNameForm.html.twig")
     * 
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $config
     * 
     * @return array
     */
    public function editDesktopWidgetNameFormAction(WidgetInstance $config)
    {   
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(FormFactory::TYPE_WIDGET_CONFIG, array(), $config);
         
        return array('form' => $form->createView(), 'config' => $config);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/name/edit/{config}",
     *     name = "claro_desktop_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:editWidgetNameForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * 
     * @return array
     */
    public function editDesktopWidgetName(WidgetInstance $config, User $user)
    {
        $form = $this->request->request->get('widget_display_form');
        $config->setName($form['name']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($config);
        $em->flush();
        
        return new Response('success', 204);
    }
    
    /**
     * @EXT\Route(
     *     "/workspace/widget/name/form/{config}",
     *     name = "claro_workspace_widget_name_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:editWidgetNameForm.html.twig")
     * 
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $config
     * 
     * @return array
     */
    public function editWorkspaceWidgetNameFormAction(WidgetInstance $config)
    {   
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(FormFactory::TYPE_WIDGET_CONFIG, array(), $config);
        
        return array('form' => $form->createView(), 'config' => $config);
    }
    
    /**
     * @EXT\Route(
     *     "/workspace/widget/name/edit/{config}",
     *     name = "claro_workspace_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:editWidgetNameForm.html.twig")
     * 
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $config
     * 
     * @return array
     */
    public function editWorkspaceWidgetName(WidgetInstance $config)
    {
        $form = $this->request->request->get('widget_display_form');
        $config->setName($form['name']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($config);
        $em->flush();
        
        return new Response('success', 204);
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

        if (!$this->securityContext->isGranted($role->getName())) {
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

    private function getHomeTool()
    {
        return $this->toolManager->getOneToolByName('home');
    }
}
