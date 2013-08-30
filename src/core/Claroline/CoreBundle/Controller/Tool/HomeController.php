<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Widget\Manager;
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
     *     "widgetManager"      = @DI\Inject("claroline.widget.manager")
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
        Manager $widgetManager
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

        $configs = $this->widgetManager
            ->generateWorkspaceDisplayConfig($workspace->getId());

        return array(
            'workspace' => $workspace,
            'configs' => $configs,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget/{widget}/baseconfig/{adminConfig}/invertvisible",
     *     name="claro_workspace_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Inverts the visibility boolean of a widget in the specified workspace.
     * If the DisplayConfig entity for the workspace doesn't exist in the database
     * yet, it's created here.
     *
     * @param AbstractWorkspace workspace
     * @param Widget        $widget
     * @param DisplayConfig $adminConfig The displayConfig defined by the administrator: it's the
     * configuration entity for widgets
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceInvertVisibleWidgetAction(
        AbstractWorkspace $workspace,
        Widget $widget,
        DisplayConfig $adminConfig
    )
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $displayConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $workspace, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setWorkspace($workspace);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(false);
        }

        $displayConfig->invertVisible();
        $this->em->persist($displayConfig);
        $this->em->flush();

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/widget/{widget}/configuration",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget            $widget
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction(AbstractWorkspace $workspace, Widget $widget)
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $event = $this->eventDispatcher->dispatch(
            "widget_{$widget->getName()}_configuration_workspace",
            'ConfigureWidgetWorkspace',
            array($workspace)
        );

        if ($event->getContent() !== '') {
            if ($this->request->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\workspace\home:widgetConfigurationForm.html.twig',
                    array(
                        'content' => $event->getContent(),
                        'workspace' => $workspace,
                        'tool' => $this->getHomeTool()
                    )
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig',
                array(
                    'content' => $event->getContent(),
                    'workspace' => $workspace,
                    'tool' => $this->getHomeTool()
                )
            );
        }

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
     *
     * @return Response
     */
    public function desktopWidgetPropertiesAction()
    {
        $user = $this->securityContext->getToken()->getUser();
        $configs = $this->widgetManager
            ->generateDesktopDisplayConfig($user->getId());

        return array(
            'configs' => $configs,
            'user' => $user,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/config/{adminConfig}/widget/{widget}/invertvisible",
     *     name="claro_desktop_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Inverts the visibility boolean for a widget for the current user.
     *
     * @param Widget        $widget      the widget
     * @param DisplayConfig $adminConfig the display config (the configuration entity for widgets)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopInvertVisibleUserWidgetAction(Widget $widget, DisplayConfig $adminConfig)
    {
        $user = $this->securityContext->getToken()->getUser();
        $displayConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('user' => $user, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setUser($user);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(true);
        }

        $displayConfig->invertVisible();
        $this->em->persist($displayConfig);
        $this->em->flush();

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/{widget}/configuration/desktop",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to display its configuration page.
     *
     * @param Widget $widget the widget
     *
     * @return Response
     */
    public function desktopConfigureWidgetAction(Widget $widget)
    {
        $user = $this->securityContext->getToken()->getUser();
        $event = $this->eventDispatcher->dispatch(
            "widget_{$widget->getName()}_configuration_desktop",
            'ConfigureWidgetDesktop',
            array($user)
        );

        if ($event->getContent() !== '') {
            if ($this->request->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\desktop\home:widgetConfigurationForm.html.twig',
                    array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig',
                array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
            );
        }
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

        return array(
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'homeTabConfigs' => $homeTabConfigs,
            'tool' => $this->getHomeTool()
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
        $request = $this->getRequest();
        $form->handleRequest($request);

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
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

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

        if ($homeTab->getUser()->getId() === $user->getId()) {
            $this->homeTabManager->deleteHomeTab($homeTab, 'desktop', $tabOrder);
        }

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

        $adminHTC = array();
        $adminHomeTabConfigs = $this->homeTabManager->generateAdminHomeTabConfigsByWorkspace($workspace);
        $homeTabConfigs = $this->homeTabManager->getWorkspaceHomeTabConfigsByWorkspace($workspace);

        return array(
            'workspace' => $workspace,
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'homeTabConfigs' => $homeTabConfigs,
            'tool' => $this->getHomeTool()
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
        $request = $this->getRequest();
        $form->handleRequest($request);

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
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

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

        if ($homeTab->getWorkspace()->getId() === $workspace->getId()) {
            $this->homeTabManager->deleteHomeTab($homeTab, 'workspace', $tabOrder);
        }

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
        $this->checkUserAccess();
        $workspace = $homeTabConfig->getWorkspace();

        if (!is_null($workspace)) {
            $this->checkWorkspaceAccess($workspace);
        }

        $isVisible = ($visible === 'visible') ? true : false;
        $this->homeTabManager->updateVisibility($homeTabConfig, $isVisible);

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

    private function getHomeTool()
    {
        return $this->toolManager->getOneToolByName('home');
    }
}
