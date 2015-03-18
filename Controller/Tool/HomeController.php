<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\HomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetDisplayType;
use Claroline\CoreBundle\Form\WidgetDisplayConfigType;
use Claroline\CoreBundle\Form\WidgetHomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetInstanceType;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *     "formFactory"        = @DI\Inject("form.factory"),
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
     *     "/desktop/tab/{tabId}",
     *     name="claro_display_desktop_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTab.html.twig")
     *
     * Displays the desktop home tab.
     *
     * @param integer $tabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayDesktopHomeTabAction(User $user, $tabId)
    {
        $adminHomeTabConfigs = $this->homeTabManager
            ->generateAdminHomeTabConfigsByUser($user);
        $visibleAdminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigs);
        $userHomeTabConfigs = $this->homeTabManager
            ->getVisibleDesktopHomeTabConfigsByUser($user);
        $homeTabId = intval($tabId);
        $firstElement = true;

        if ($homeTabId !== -1) {
            foreach ($visibleAdminHomeTabConfigs as $adminHomeTabConfig) {
                if ($homeTabId === $adminHomeTabConfig->getHomeTab()->getId()) {
                    $firstElement = false;
                    break;
                }
            }
            if ($firstElement) {
                foreach ($userHomeTabConfigs as $userHomeTabConfig) {
                    if ($homeTabId === $userHomeTabConfig->getHomeTab()->getId()) {
                        $firstElement = false;
                        break;
                    }
                }
            }
        }

        if ($firstElement) {
            $firstAdminHomeTabConfig = reset($visibleAdminHomeTabConfigs);

            if ($firstAdminHomeTabConfig) {
                $homeTabId = $firstAdminHomeTabConfig->getHomeTab()->getId();
            } else {
                $firstHomeTabConfig = reset($userHomeTabConfigs);

                if ($firstHomeTabConfig) {
                    $homeTabId = $firstHomeTabConfig->getHomeTab()->getId();
                }
            }
        }

        return array(
            'adminHomeTabConfigs' => $visibleAdminHomeTabConfigs,
            'userHomeTabConfigs' => $userHomeTabConfigs,
            'tabId' => $homeTabId
        );
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/form/{widgetInstance}",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Asks a widget to render its configuration page for desktop.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return Response
     */
    public function getDesktopWidgetFormConfigurationAction(
        User $user,
        WidgetInstance $widgetInstance
    )
    {
        $this->checkUserAccessForWidgetInstance($widgetInstance, $user);

        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/widget/form/{widgetInstance}",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param WidgetInstance $widgetInstance
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function getWorkspaceWidgetFormConfigurationAction(
        Workspace $workspace,
        WidgetInstance $widgetInstance
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForWidgetInstance($widgetInstance, $workspace);

        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/widget/content/{widgetInstance}",
     *     name="claro_widget_content",
     *     options={"expose"=true}
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
     *     "desktop/hometab/{homeTab}/widget/instance/create/form",
     *     name="claro_desktop_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetInstanceCreateModalForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @return Response
     */
    public function desktopWidgetInstanceCreateFormAction(HomeTab $homeTab)
    {
        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType(true),
            new WidgetInstance()
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            new WidgetDisplayConfig()
        );

        return array(
            'homeTab' => $homeTab,
            'instanceForm' => $instanceForm->createView(),
            'displayConfigForm' => $displayConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/hometab/{homeTab}/widget/instance/create",
     *     name="claro_desktop_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetInstanceCreateModalForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @return Response
     */
    public function desktopWidgetInstanceCreateAction(User $user, HomeTab $homeTab)
    {
        $widgetInstance = new WidgetInstance();
        $widgetDisplayConfig = new WidgetDisplayConfig();

        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType(true),
            $widgetInstance
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );
        $instanceForm->handleRequest($this->request);
        $displayConfigForm->handleRequest($this->request);

        if ($instanceForm->isValid() && $displayConfigForm->isValid()) {
            $widgetInstance->setUser($user);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(true);
            $widgetHomeTabConfig = new WidgetHomeTabConfig();
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setUser($user);
            $widgetHomeTabConfig->setVisible(true);
            $widgetHomeTabConfig->setLocked(false);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('desktop');
            $widget = $widgetInstance->getWidget();
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setUser($user);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );

            return new JsonResponse($widgetInstance->getId(), 200);
        } else {

            return array(
                'homeTab' => $homeTab,
                'instanceForm' => $instanceForm->createView(),
                'displayConfigForm' => $displayConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit/form",
     *     name="claro_desktop_widget_config_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetEditModalForm.html.twig")
     *
     * Displays the widget config form.
     *
     * @return Response
     */
    public function desktopWidgetConfigEditFormAction(
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig,
        User $user
    )
    {
        $this->checkUserAccessForWidgetInstance($widgetInstance, $user);
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);
        $this->checkUserAccessForWidgetDisplayConfig($widgetDisplayConfig, $user);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );

        return array(
            'instanceForm' => $instanceForm->createView(),
            'displayConfigForm' => $displayConfigForm->createView(),
            'widgetInstance' => $widgetInstance,
            'widgetHomeTabConfig' => $widgetHomeTabConfig,
            'widgetDisplayConfig' => $widgetDisplayConfig
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit",
     *     name="claro_desktop_widget_config_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopWidgetEditModalForm.html.twig")
     *
     * Edit the widget config.
     *
     * @return Response
     */
    public function desktopWidgetConfigEditAction(
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig,
        User $user
    )
    {
        $this->checkUserAccessForWidgetInstance($widgetInstance, $user);
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);
        $this->checkUserAccessForWidgetDisplayConfig($widgetDisplayConfig, $user);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );
        $instanceForm->handleRequest($this->request);
        $displayConfigForm->handleRequest($this->request);

        if ($instanceForm->isValid() && $displayConfigForm->isValid()) {
            $this->widgetManager->insertWidgetInstance($widgetInstance);
            $this->widgetManager->persistWidgetDisplayConfigs(array($widgetDisplayConfig));

            return new JsonResponse(
                array(
                    'id' => $widgetHomeTabConfig->getId(),
                    'color' => $widgetDisplayConfig->getColor(),
                    'title' => $widgetInstance->getName()
                ),
                200
            );
        } else {

            return array(
                'instanceForm' => $instanceForm->createView(),
                'displayConfigForm' => $displayConfigForm->createView(),
                'widgetInstance' => $widgetInstance,
                'widgetHomeTabConfig' => $widgetHomeTabConfig,
                'widgetDisplayConfig' => $widgetDisplayConfig
            );
        }
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/hometab/{homeTab}/widget/instance/create/form",
     *     name="claro_workspace_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetInstanceCreateModalForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceWidgetInstanceCreateFormAction(
        Workspace $workspace,
        HomeTab $homeTab
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);

        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType(false),
            new WidgetInstance()
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(),
            new WidgetHomeTabConfig()
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            new WidgetDisplayConfig()
        );

        return array(
            'workspace' => $workspace,
            'homeTab' => $homeTab,
            'instanceForm' => $instanceForm->createView(),
            'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
            'displayConfigForm' => $displayConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/hometab/{homeTab}/widget/instance/create",
     *     name="claro_workspace_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetInstanceCreateModalForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceWidgetInstanceCreateAction(
        Workspace $workspace,
        HomeTab $homeTab
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);

        $widgetInstance = new WidgetInstance();
        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetDisplayConfig = new WidgetDisplayConfig();

        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType(false),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(),
            $widgetHomeTabConfig
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );
        $instanceForm->handleRequest($this->request);
        $widgetHomeTabConfigForm->handleRequest($this->request);
        $displayConfigForm->handleRequest($this->request);

        if ($instanceForm->isValid() &&
            $widgetHomeTabConfigForm->isValid() &&
            $displayConfigForm->isValid()) {

            $widgetInstance->setWorkspace($workspace);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(false);
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setWorkspace($workspace);
            $widgetHomeTabConfig->setLocked(false);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('workspace');
            $widget = $widgetInstance->getWidget();
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setWorkspace($workspace);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );

            return new JsonResponse($widgetInstance->getId(), 200);
        } else {

            return array(
                'workspace' => $workspace,
                'homeTab' => $homeTab,
                'instanceForm' => $instanceForm->createView(),
                'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
                'displayConfigForm' => $displayConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit/form",
     *     name="claro_workspace_widget_config_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetEditModalForm.html.twig")
     *
     * Displays the widget config form.
     *
     * @return Response
     */
    public function workspaceWidgetConfigEditFormAction(
        Workspace $workspace,
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForWidgetInstance($widgetInstance, $workspace);
        $this->checkWorkspaceAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $workspace);
        $this->checkWorkspaceAccessForWidgetDisplayConfig($widgetDisplayConfig, $workspace);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(),
            $widgetHomeTabConfig
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );

        return array(
            'workspace' => $workspace,
            'instanceForm' => $instanceForm->createView(),
            'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
            'displayConfigForm' => $displayConfigForm->createView(),
            'widgetInstance' => $widgetInstance,
            'widgetHomeTabConfig' => $widgetHomeTabConfig,
            'widgetDisplayConfig' => $widgetDisplayConfig
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit",
     *     name="claro_workspace_widget_config_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceWidgetEditModalForm.html.twig")
     *
     * Edit the widget config.
     *
     * @return Response
     */
    public function workspaceWidgetConfigEditAction(
        Workspace $workspace,
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForWidgetInstance($widgetInstance, $workspace);
        $this->checkWorkspaceAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $workspace);
        $this->checkWorkspaceAccessForWidgetDisplayConfig($widgetDisplayConfig, $workspace);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(),
            $widgetHomeTabConfig
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );
        $instanceForm->handleRequest($this->request);
        $widgetHomeTabConfigForm->handleRequest($this->request);
        $displayConfigForm->handleRequest($this->request);

        if ($instanceForm->isValid() &&
            $widgetHomeTabConfigForm->isValid() &&
            $displayConfigForm->isValid()) {

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );
            $visibility = $widgetHomeTabConfig->isVisible() ?
                'visible' :
                'hidden';

            return new JsonResponse(
                array(
                    'id' => $widgetHomeTabConfig->getId(),
                    'color' => $widgetDisplayConfig->getColor(),
                    'title' => $widgetInstance->getName(),
                    'visibility' => $visibility
                ),
                200
            );
        } else {

            return array(
                'workspace' => $workspace,
                'instanceForm' => $instanceForm->createView(),
                'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
                'displayConfigForm' => $displayConfigForm->createView(),
                'widgetInstance' => $widgetInstance,
                'widgetHomeTabConfig' => $widgetHomeTabConfig,
                'widgetDisplayConfig' => $widgetDisplayConfig
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/update/widgets/display/config",
     *     name="claro_desktop_update_widgets_display_config",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "widgetDisplayConfigs",
     *      class="ClarolineCoreBundle:Widget\WidgetDisplayConfig",
     *      options={"multipleIds" = true, "name" = "wdcIds"}
     * )
     */
    public function updateDesktopWidgetsDisplayConfigAction(
        User $user,
        array $widgetDisplayConfigs
    )
    {
        $toPersist = array();

        foreach ($widgetDisplayConfigs as $config) {

            $this->checkUserAccessForWidgetDisplayConfig($config, $user);
        }
        $datas = $this->request->request->all();

        foreach ($widgetDisplayConfigs as $config) {
            $id = $config->getId();

            if (isset($datas[$id]) && !empty($datas[$id])) {
                $config->setRow($datas[$id]['row']);
                $config->setColumn($datas[$id]['column']);
                $config->setWidth($datas[$id]['width']);
                $config->setHeight($datas[$id]['height']);
                $toPersist[] = $config;
            }
        }

        if (count($toPersist) > 0) {
            $this->widgetManager->persistWidgetDisplayConfigs($toPersist);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/update/widgets/display/config",
     *     name="claro_workspace_update_widgets_display_config",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetDisplayConfigs",
     *      class="ClarolineCoreBundle:Widget\WidgetDisplayConfig",
     *      options={"multipleIds" = true, "name" = "wdcIds"}
     * )
     */
    public function updateWorkspaceWidgetsDisplayConfigAction(
        Workspace $workspace,
        array $widgetDisplayConfigs
    )
    {
        $toPersist = array();

        foreach ($widgetDisplayConfigs as $config) {

            $this->checkWorkspaceAccessForWidgetDisplayConfig($config, $workspace);
        }
        $datas = $this->request->request->all();

        foreach ($widgetDisplayConfigs as $config) {
            $id = $config->getId();

            if (isset($datas[$id]) && !empty($datas[$id])) {
                $config->setRow($datas[$id]['row']);
                $config->setColumn($datas[$id]['column']);
                $config->setWidth($datas[$id]['width']);
                $config->setHeight($datas[$id]['height']);
                $toPersist[] = $config;
            }
        }

        if (count($toPersist) > 0) {
            $this->widgetManager->persistWidgetDisplayConfigs($toPersist);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create/form",
     *     name="claro_desktop_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabCreateModalForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function desktopHomeTabCreateFormAction()
    {
        $form = $this->formFactory->create(new HomeTabType, new HomeTab());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create",
     *     name="claro_desktop_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabCreateModalForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function desktopHomeTabCreateAction(User $user)
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(new HomeTabType, $homeTab);
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
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return new JsonResponse($homeTab->getId(), 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTab}/edit/form",
     *     name="claro_desktop_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabEditModalForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @param HomeTab $homeTab
     *
     * @return Response
     */
    public function desktopHomeTabEditFormAction(User $user, HomeTab $homeTab)
    {
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(new HomeTabType, $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTab}/edit",
     *     name="claro_desktop_home_tab_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabEditModalForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @param HomeTab $homeTab
     *
     * @return Response
     */
    public function desktopHomeTabEditAction(User $user, HomeTab $homeTab)
    {
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $form = $this->formFactory->create(new HomeTabType, $homeTab);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return new JsonResponse(
                array('id' => $homeTab->getId(), 'name' => $homeTab->getName()),
                200
            );
        } else {

            return array(
                'form' => $form->createView(),
                'homeTab' => $homeTab
            );
        }
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTab}/delete",
     *     name="claro_desktop_home_tab_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Delete the given homeTab.
     *
     * @param HomeTab $homeTab
     * @param integer $tabOrder
     *
     * @return Response
     */
    public function desktopHomeTabDeleteAction(User $user, HomeTab $homeTab)
    {
        $this->checkUserAccessForHomeTab($homeTab, $user);
        $this->homeTabManager->deleteHomeTab($homeTab);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab_config/{homeTabConfig}/reorder/next/{nextHomeTabConfigId}",
     *     name="claro_desktop_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Update desktop HomeTabConfig order
     *
     * @return Response
     */
    public function desktopHomeTabConfigReorderAction(
        User $user,
        HomeTabConfig $homeTabConfig,
        $nextHomeTabConfigId
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkUserAccessForHomeTab($homeTab, $user);

        $this->homeTabManager->reorderDesktopHomeTabConfigs(
            $user,
            $homeTabConfig,
            $nextHomeTabConfigId
        );

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/user/home_tab/create/form",
     *     name="claro_workspace_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabCreateModalForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function workspaceHomeTabCreateFormAction(Workspace $workspace)
    {
        $this->checkWorkspaceEditionAccess($workspace);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
            new HomeTab()
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(),
            new HomeTabConfig()
        );

        return array(
            'workspace' => $workspace,
            'homeTabForm' => $homeTabForm->createView(),
            'homeTabConfigForm' => $homeTabConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/home_tab/create",
     *     name="claro_workspace_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabCreateModalForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabCreateAction(Workspace $workspace)
    {
        $this->checkWorkspaceEditionAccess($workspace);

        $homeTab = new HomeTab();
        $homeTabConfig = new HomeTabConfig();
        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(),
            $homeTabConfig
        );
        $homeTabForm->handleRequest($this->request);
        $homeTabConfigForm->handleRequest($this->request);

        if ($homeTabForm->isValid() && $homeTabConfigForm->isValid()) {
            $homeTab->setType('workspace');
            $homeTab->setWorkspace($workspace);
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('workspace');
            $homeTabConfig->setWorkspace($workspace);
            $homeTabConfig->setLocked(false);

            $lastOrder = $this->homeTabManager
                ->getOrderOfLastWorkspaceHomeTabConfigByWorkspace($workspace);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return new JsonResponse($homeTab->getId(), 200);
        } else {

            return array(
                'workspace' => $workspace,
                'homeTabForm' => $homeTabForm->createView(),
                'homeTabConfigForm' => $homeTabConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/home_tab/{homeTab}/config/{homeTabConfig}/edit/form",
     *     name="claro_workspace_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabEditModalForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function workspaceHomeTabEditFormAction(
        Workspace $workspace,
        HomeTab $homeTab,
        HomeTabConfig $homeTabConfig
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(),
            $homeTabConfig
        );

        return array(
            'workspace' => $workspace,
            'homeTabForm' => $homeTabForm->createView(),
            'homeTabConfigForm' => $homeTabConfigForm->createView(),
            'homeTab' => $homeTab,
            'homeTabConfig' => $homeTabConfig
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/home_tab/{homeTab}/config/{homeTabConfig}/edit",
     *     name="claro_workspace_home_tab_edit"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabEditModalForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabEditAction(
        Workspace $workspace,
        HomeTab $homeTab,
        HomeTabConfig $homeTabConfig
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(),
            $homeTabConfig
        );
        $homeTabForm->handleRequest($this->request);
        $homeTabConfigForm->handleRequest($this->request);

        if ($homeTabForm->isValid() && $homeTabConfigForm->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);
            $visibility = $homeTabConfig->isVisible() ?
                'visible' :
                'hidden';

            return new JsonResponse(
                array(
                    'id' => $homeTab->getId(),
                    'name' => $homeTab->getName(),
                    'visibility' => $visibility
                ),
                200
            );
        } else {

            return array(
                'workspace' => $workspace,
                'homeTabForm' => $homeTabForm->createView(),
                'homeTabConfigForm' => $homeTabConfigForm->createView(),
                'homeTab' => $homeTab,
                'homeTabConfig' => $homeTabConfig
            );
        }
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/home_tab/{homeTab}/delete",
     *     name="claro_workspace_home_tab_delete",
     *     options = {"expose"=true}
     * )
     *
     * Delete the given homeTab.
     *
     * @return Response
     */
    public function workspaceHomeTabDeleteAction(
        Workspace $workspace,
        HomeTab $homeTab
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);
        $this->homeTabManager->deleteHomeTab($homeTab);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/home_tab_config/{homeTabConfig}/reorder/next/{nextHomeTabConfigId}",
     *     name="claro_workspace_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Update workspace HomeTabConfig order
     *
     * @return Response
     */
    public function workspaceHomeTabConfigReorderAction(
        Workspace $workspace,
        HomeTabConfig $homeTabConfig,
        $nextHomeTabConfigId
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceAccessForHomeTab($homeTab, $workspace);

        $this->homeTabManager->reorderWorkspaceHomeTabConfigs(
            $workspace,
            $homeTabConfig,
            $nextHomeTabConfigId
        );

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/visibility/{visible}/update",
     *     name="claro_home_tab_update_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
    public function homeTabUpdateVisibilityAction(
        User $user,
        HomeTabConfig $homeTabConfig,
        $visible
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkUserAccessForAdminHomeTab($homeTab, $user);

        $isVisible = ($visible === 'visible') ? true : false;
        $this->homeTabManager->updateVisibility($homeTabConfig, $isVisible);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget_home_tab_config/{widgetHomeTabConfig}/change/visibility",
     *     name="claro_desktop_widget_home_tab_config_change_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Change visibility of the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function desktopWidgetHomeTabConfigChangeVisibilityAction(
        User $user,
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkUserAccessForWidgetHomeTabConfig($widgetHomeTabConfig, $user);

        $this->homeTabManager->changeVisibilityWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget_home_tab_config/{widgetHomeTabConfig}/delete",
     *     name="claro_desktop_widget_home_tab_config_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Delete the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function desktopWidgetHomeTabConfigDeleteAction(
        User $user,
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
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
     *     "/workspace/{workspace}/widget_home_tab_config/{widgetHomeTabConfig}/delete",
     *     name="claro_workspace_widget_home_tab_config_delete",
     *     options = {"expose"=true}
     * )
     *
     * Delete the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function workspaceWidgetHomeTabConfigDeleteAction(
        Workspace $workspace,
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkWorkspaceEditionAccess($workspace);
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
        } else {
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
        Workspace $workspace
    )
    {
        $homeTabWorkspace = $homeTab->getWorkspace();

        if (is_null($homeTabWorkspace) || ($homeTabWorkspace->getId() !== $workspace->getId())) {
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

    private function checkUserAccessForWidgetDisplayConfig(
        WidgetDisplayConfig $widgetDisplayConfig,
        User $user
    )
    {
        $widgetDisplayConfigUser = $widgetDisplayConfig->getUser();

        if (is_null($widgetDisplayConfigUser) ||
            ($widgetDisplayConfigUser->getId() !== $user->getId())) {

            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForWidgetDisplayConfig(
        WidgetDisplayConfig $widgetDisplayConfig,
        Workspace $workspace
    )
    {
        $widgetDisplayConfigWorkspace = $widgetDisplayConfig->getWorkspace();

        if (is_null($widgetDisplayConfigWorkspace) ||
            ($widgetDisplayConfigWorkspace->getId() !== $workspace->getId())) {

            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceAccessForWidgetHomeTabConfig(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        Workspace $workspace
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
        Workspace $workspace
    )
    {
        $widgetWorkspace = $widgetInstance->getWorkspace();

        if (is_null($widgetWorkspace) || ($widgetWorkspace->getId() !== $workspace->getId())) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceEditionAccess(Workspace $workspace)
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {

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
        Workspace $workspace
    )
    {
        $widgetWorkspace = $widgetInstance->getWorkspace();

        if (is_null($widgetWorkspace) || ($widgetWorkspace->getId() !== $workspace->getId())) {
            return false;
        }

        return true;
    }
}
