<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AdministrationHomeTabController extends Controller
{
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "widgetManager"      = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        WidgetManager $widgetManager
    )
    {
        $this->formFactory = $formFactory;
        $this->homeTabManager = $homeTabManager;
        $this->request = $request;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/home_tabs/configuration/menu",
     *     name="claro_admin_home_tabs_configuration_menu",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminHomeTabsConfigMenu.html.twig")
     *
     * Displays the homeTabs configuration menu.
     *
     * @return Response
     */
    public function adminHomeTabsConfigMenuAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/desktop/home_tabs/{homeTabId}/configuration",
     *     name="claro_admin_desktop_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminDesktopHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @return Response
     */
    public function adminDesktopHomeTabsConfigAction($homeTabId = -1)
    {
        $homeTabConfigs = $this->homeTabManager
            ->getAdminDesktopHomeTabConfigs();

        $tabId = intval($homeTabId);
        $widgetHomeTabConfigs = array();
        $lastWidgetOrder = 1;

        if ($tabId === 0) {
            $homeTabConfig = end($homeTabConfigs);

            if ($homeTabConfig !== false) {
                $homeTab = $homeTabConfig->getHomeTab();
                $tabId = $homeTab->getId();
                $widgetHomeTabConfigs = $this->homeTabManager
                    ->getAdminWidgetConfigs($homeTab);

                $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);
            }
        } else {
            foreach ($homeTabConfigs as $homeTabConfig) {
                $homeTab = $homeTabConfig->getHomeTab();

                if ($tabId === -1) {
                    $tabId = $homeTab->getId();
                    $widgetHomeTabConfigs = $this->homeTabManager
                        ->getAdminWidgetConfigs($homeTab);
                    $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);
                } elseif ($tabId === $homeTab->getId()) {
                    $widgetHomeTabConfigs = $this->homeTabManager
                        ->getAdminWidgetConfigs($homeTab);
                    $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);
                }
            }
        }

        return array(
            'curentHomeTabId' => $tabId,
            'homeTabConfigs' => $homeTabConfigs,
            'widgetHomeTabConfigs' => $widgetHomeTabConfigs,
            'lastWidgetOrder' => $lastWidgetOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/home_tabs/{homeTabId}/configuration",
     *     name="claro_admin_workspace_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminWorkspaceHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabsConfigAction($homeTabId = -1)
    {
        $homeTabConfigs = $this->homeTabManager
            ->getAdminWorkspaceHomeTabConfigs();

        $tabId = intval($homeTabId);
        $widgetHomeTabConfigs = array();
        $lastWidgetOrder = 1;

        if ($tabId === 0) {
            $homeTabConfig = end($homeTabConfigs);

            if ($homeTabConfig !== false) {
                $homeTab = $homeTabConfig->getHomeTab();
                $tabId = $homeTab->getId();
                $widgetHomeTabConfigs = $this->homeTabManager
                    ->getAdminWidgetConfigs($homeTab);
                $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);
            }
        } else {
            foreach ($homeTabConfigs as $homeTabConfig) {
                $homeTab = $homeTabConfig->getHomeTab();

                if ($tabId === -1) {
                    $tabId = $homeTab->getId();
                    $widgetHomeTabConfigs = $this->homeTabManager
                        ->getAdminWidgetConfigs($homeTab);
                    $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);

                } elseif ($tabId === $homeTab->getId()) {
                    $widgetHomeTabConfigs = $this->homeTabManager
                        ->getAdminWidgetConfigs($homeTab);
                    $lastWidgetOrder = $this->getOrderOfLastWidgetInHomeTab($homeTab);
                }
            }
        }

        return array(
            'curentHomeTabId' => $tabId,
            'homeTabConfigs' => $homeTabConfigs,
            'widgetHomeTabConfigs' => $widgetHomeTabConfigs,
            'lastWidgetOrder' => $lastWidgetOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/create/form",
     *     name="claro_admin_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminHomeTabCreateForm.html.twig")
     *
     * Displays the admin homeTab form.
     *
     * @return Response
     */
    public function adminHomeTabCreateFormAction()
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabType}/create",
     *     name="claro_admin_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminHomeTabCreateForm.html.twig")
     *
     * Create a new admin homeTab.
     *
     * @return Response
     */
    public function adminHomeTabCreateAction($homeTabType)
    {
        $isDesktop = ($homeTabType === 'desktop');
        $type = $isDesktop ? 'admin_desktop' : 'admin_workspace';

        $homeTab = new HomeTab();

        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $homeTab->setType($type);
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType($type);
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);
            $lastOrder = $isDesktop ?
                $this->homeTabManager->getOrderOfLastAdminDesktopHomeTabConfig() :
                $this->homeTabManager->getOrderOfLastAdminWorkspaceHomeTabConfig();

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
     *     "/home_tab/{homeTabId}/edit/form",
     *     name="claro_admin_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminHomeTabEditForm.html.twig")
     *
     * Displays the admin homeTab name edition form.
     *
     * @return Response
     */
    public function adminHomeTabEditFormAction(HomeTab $homeTab)
    {
        if (!is_null($homeTab->getUser()) ||
            !is_null($homeTab->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array('form' => $form->createView(), 'homeTab' => $homeTab);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/edit",
     *     name="claro_admin_home_tab_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminHomeTabEditForm.html.twig")
     *
     * Edit the admin homeTab name.
     *
     * @return Response
     */
    public function adminHomeTabEditAction(HomeTab $homeTab)
    {
        if (!is_null($homeTab->getUser()) ||
            !is_null($homeTab->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return new Response('success', 204);
        }

        return array('form' => $form->createView(), 'homeTab' => $homeTab);
    }

    /**
     * @EXT\Route(
     *     "/home_tab_config/{homeTabConfigId}/delete",
     *     name="claro_admin_home_tab_config_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Delete the given homeTabConfig and corresponding homeTab.
     *
     * @return Response
     */
    public function adminHomeTabConfigDeleteAction(HomeTabConfig $homeTabConfig)
    {
        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $homeTab = $homeTabConfig->getHomeTab();
        $type = $homeTab->getType();
        $tabOrder = $homeTabConfig->getTabOrder();
        $this->homeTabManager->deleteHomeTab($homeTab, $type, $tabOrder);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/visibility/{visible}/update",
     *     name="claro_admin_home_tab_update_visibility",
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
    public function adminHomeTabUpdateVisibilityAction(
        HomeTabConfig $homeTabConfig,
        $visible
    )
    {
        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $isVisible = ($visible === 'visible') ? true : false;
        $this->homeTabManager->updateVisibility($homeTabConfig, $isVisible);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/lock/{locked}/update",
     *     name="claro_admin_home_tab_update_lock",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Configure lock of an Home tab
     *
     * @return Response
     */
    public function adminHomeTabUpdateLockAction(
        HomeTabConfig $homeTabConfig,
        $locked
    )
    {
        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $isLocked = ($locked === 'locked') ? true : false;
        $this->homeTabManager->updateLock($homeTabConfig, $isLocked);

        return new Response('success', 204);
    }

    private function getOrderOfLastWidgetInHomeTab(HomeTab $homeTab)
    {
        $lastOrder = 1;
        $lastWidgetOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInAdminHomeTab($homeTab);

        if (!is_null($lastWidgetOrder)) {
            $lastOrder = $lastWidgetOrder['order_max'];
        }

        return $lastOrder;
    }

    /**
     * @EXT\Route(
     *     "/widget_home_tab_config/{widgetHomeTabConfigId}/delete",
     *     name="claro_admin_widget_home_tab_config_delete",
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
    public function adminWidgetHomeTabConfigDeleteAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        if (!is_null($widgetHomeTabConfig->getUser()) ||
            !is_null($widgetHomeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
        $this->homeTabManager->deleteWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );
        $this->widgetManager->removeInstance($widgetInstance);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "widget/instance/{homeTabType}/create/form",
     *     name="claro_admin_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminWidgetInstanceCreateForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @return Response
     */
    public function adminWidgetInstanceCreateFormAction($homeTabType)
    {
        $widgetInstance = new WidgetInstance();
        $isDesktop = ($homeTabType === 'desktop');
        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array('desktop_widget' => $isDesktop),
            $widgetInstance
        );

        return array(
            'form' => $form->createView(),
            'homeTabType' => $homeTabType
        );
    }

    /**
     * @EXT\Route(
     *     "widget/instance/{homeTabType}/create",
     *     name="claro_admin_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminWidgetInstanceCreateForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @return Response
     */
    public function adminWidgetInstanceCreateAction($homeTabType)
    {
        $widgetInstance = new WidgetInstance();
        $isDesktop = ($homeTabType === 'desktop');

        $form = $this->formFactory->create(
            FormFactory::TYPE_WIDGET_INSTANCE,
            array('desktop_widget' => $isDesktop),
            $widgetInstance
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $widgetInstance->setIsAdmin(true);
            $widgetInstance->setIsDesktop($isDesktop);

            $this->widgetManager->insertWidgetInstance($widgetInstance);

            return new Response($widgetInstance->getId(), 201);
        }

        return array(
            'form' => $form->createView(),
            'homeTabType' => $homeTabType
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/associate/widget/{widgetInstanceId}",
     *     name="claro_admin_associate_widget_to_home_tab",
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
    public function associateWidgetToHomeTabAction(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance
    )
    {
        if (!is_null($homeTab->getUser()) ||
            !is_null($homeTab->getWorkspace()) ||
            !is_null($widgetInstance->getUser()) ||
            !is_null($widgetInstance->getWorkspace())) {

            throw new AccessDeniedException();
        }

        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
        $widgetHomeTabConfig->setVisible(true);
        $widgetHomeTabConfig->setLocked(false);
        $widgetHomeTabConfig->setType('admin');

        $lastOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInAdminHomeTab($homeTab);

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
     *     "/widget/{widgetInstanceId}/name/edit/form",
     *     name = "claro_admin_widget_instance_name_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminWidgetInstanceNameEditForm.html.twig")
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function adminWidgetInstanceNameFormAction(WidgetInstance $widgetInstance)
    {
        if (!is_null($widgetInstance->getUser()) ||
            !is_null($widgetInstance->getWorkspace())) {

            throw new AccessDeniedException();
        }

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
     *     "/widget/{widgetInstanceId}/name/edit",
     *     name = "claro_admin_widget_instance_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\new:adminWidgetInstanceNameEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return array
     */
    public function adminWidgetInstanceNameAction(WidgetInstance $widgetInstance)
    {
        if (!is_null($widgetInstance->getUser()) ||
            !is_null($widgetInstance->getWorkspace())) {

            throw new AccessDeniedException();
        }
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

    /***********************
     *          OLD
     **********************/

    /**
     * @EXT\Route(
     *     "/home_tabs/configuration",
     *     name="claro_admin_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @return Response
     */
    public function adminHomeTabsConfigAction()
    {
        $desktopHomeTabConfigs = $this->homeTabManager
            ->getAdminDesktopHomeTabConfigs();
        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getAdminWorkspaceHomeTabConfigs();

        $nbWidgets = array();

        foreach ($desktopHomeTabConfigs as $desktopHomeTabConfig) {
            $widgetConfigs = $this->homeTabManager
                ->getVisibleAdminWidgetConfigs($desktopHomeTabConfig->getHomeTab());
            $nbWidgets[$desktopHomeTabConfig->getId()] = count($widgetConfigs);
        }

        foreach ($workspaceHomeTabConfigs as $workspaceHomeTabConfig) {
            $widgetConfigs = $this->homeTabManager
                ->getVisibleAdminWidgetConfigs($workspaceHomeTabConfig->getHomeTab());
            $nbWidgets[$workspaceHomeTabConfig->getId()] = count($widgetConfigs);
        }

        return array(
            'desktopHomeTabConfigs' => $desktopHomeTabConfigs,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
            'nbWidgets' => $nbWidgets
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/desktop/create/form",
     *     name="claro_admin_desktop_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabCreateForm.html.twig")
     *
     * Displays the admin desktop homeTab form.
     *
     * @return Response
     */
    public function adminDesktopHomeTabCreateFormAction()
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/home_tab/desktop/create",
     *     name="claro_admin_desktop_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabCreateForm.html.twig")
     *
     * Create a new admin desktop homeTab.
     *
     * @return Response
     */
    public function adminDesktopHomeTabCreateAction()
    {
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $homeTab->setType('admin_desktop');
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('admin_desktop');
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);
            $lastOrder = $this->homeTabManager
                ->getOrderOfLastAdminDesktopHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/home_tab/workspace/create/form",
     *     name="claro_admin_workspace_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabCreateForm.html.twig")
     *
     * Displays the admin workspace homeTab form.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabCreateFormAction()
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/home_tab/workspace/create",
     *     name="claro_admin_workspace_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabCreateForm.html.twig")
     *
     * Create a new admin workspace homeTab.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabCreateAction()
    {
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $homeTab->setType('admin_workspace');
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('admin_workspace');
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);
            $lastOrder = $this->homeTabManager
                ->getOrderOfLastAdminWorkspaceHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/desktop/edit/form",
     *     name="claro_admin_desktop_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Displays the admin desktop homeTab edition form.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditFormAction(
        HomeTabConfig $homeTabConfig
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/workspace/edit/form",
     *     name="claro_admin_workspace_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Displays the admin workspace homeTab edition form.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditFormAction(
        HomeTabConfig $homeTabConfig
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/desktop/{homeTabName}/edit",
     *     name="claro_admin_desktop_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Edit the admin desktop homeTab.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditAction(
        HomeTabConfig $homeTabConfig,
        $homeTabName
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/workspace/{homeTabName}/edit",
     *     name="claro_admin_workspace_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Edit the admin workspace homeTab.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditAction(
        HomeTabConfig $homeTabConfig,
        $homeTabName
    )
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(
            FormFactory::TYPE_HOME_TAB,
            array(),
            $homeTab
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/{tabOrder}/delete",
     *     name="claro_admin_home_tab_delete",
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
    public function adminHomeTabDeleteAction(HomeTab $homeTab, $tabOrder)
    {
        if (is_null($homeTab->getUser()) && is_null($homeTab->getWorkspace())) {
            $type = $homeTab->getType();
            $this->homeTabManager->deleteHomeTab($homeTab, $type, $tabOrder);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/widgets/configuration",
     *     name="claro_admin_home_tab_widgets_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminHomeTabWidgetsConfig.html.twig")
     *
     * Displays the widgets configuration page for given Home tab.
     *
     * @return Response
     */
    public function adminHomeTabWidgetsConfigAction(HomeTab $homeTab)
    {
        $widgetConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);
        $lastWidgetOrder = $this->homeTabManager
            ->getOrderOfLastWidgetInAdminHomeTab($homeTab);
        $lastOrder = is_null($lastWidgetOrder) ? 1 : $lastWidgetOrder['order_max'];

        return array(
            'homeTab' => $homeTab,
            'widgetConfigs' => $widgetConfigs,
            'lastWidgetOrder' => $lastOrder
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/widgets/available/list",
     *     name="claro_admin_home_tab_addable_widgets_list",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:listAddableWidgets.html.twig")
     *
     * Displays the list of widgets that can be added to the given Home tab.
     *
     * @return Response
     */
    public function listAddableWidgetsAction(HomeTab $homeTab)
    {
        $widgetConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);
        $currentWidgetInstanceList = array();

        foreach ($widgetConfigs as $widgetConfig) {
            $currentWidgetInstanceList[] = $widgetConfig->getWidgetInstance()->getId();
        }

        if ($homeTab->getType() === 'admin_desktop') {
            $widgetInstances = $this->widgetManager
                ->getAdminDesktopWidgetInstance($currentWidgetInstanceList);
        }
        else {
            $widgetInstances = $this->widgetManager
                ->getAdminWorkspaceWidgetInstance($currentWidgetInstanceList);
        }

        return array(
            'homeTab' => $homeTab,
            'widgetInstances' => $widgetInstances
        );
    }

    /**
     * @EXT\Route(
     *     "/widget_home_tab_config/{widgetHomeTabConfigId}/change/order/{direction}",
     *     name="claro_admin_widget_home_tab_config_change_order",
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
    public function adminWidgetHomeTabConfigChangeOrderAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        $direction
    )
    {
        if (is_null($widgetHomeTabConfig->getUser()) &&
            is_null($widgetHomeTabConfig->getWorkspace())) {

            $this->homeTabManager->changeOrderWidgetHomeTabConfig(
                $widgetHomeTabConfig,
                $direction
            );
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/widget_home_tab_config/{widgetHomeTabConfigId}/change/visibility",
     *     name="claro_admin_widget_home_tab_config_change_visibility",
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
    public function adminWidgetHomeTabConfigChangeVisibilityAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        if (is_null($widgetHomeTabConfig->getUser()) &&
            is_null($widgetHomeTabConfig->getWorkspace())) {

            $this->homeTabManager->changeVisibilityWidgetHomeTabConfig(
                $widgetHomeTabConfig
            );
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/widget_home_tab_config/{widgetHomeTabConfigId}/change/lock",
     *     name="claro_admin_widget_home_tab_config_change_lock",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetHomeTabConfig",
     *     class="ClarolineCoreBundle:Widget\WidgetHomeTabConfig",
     *     options={"id" = "widgetHomeTabConfigId", "strictId" = true}
     * )
     *
     * Change lock of the given widgetHomeTabConfig.
     *
     * @return Response
     */
    public function adminWidgetHomeTabConfigChangeLockAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        if (is_null($widgetHomeTabConfig->getUser()) &&
            is_null($widgetHomeTabConfig->getWorkspace())) {

            $this->homeTabManager->changeLockWidgetHomeTabConfig(
                $widgetHomeTabConfig
            );
        }

        return new Response('success', 204);
    }
}