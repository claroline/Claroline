<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;

class HomeTabController extends Controller
{
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $widgetManager;
    private $sc;
    private $hometabAdminTool;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "homeTabManager" = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"        = @DI\Inject("request"),
     *     "widgetManager"  = @DI\Inject("claroline.manager.widget_manager"),
     *     "sc"             = @DI\Inject("security.context"),
     *     "toolManager"    = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        WidgetManager $widgetManager,
        SecurityContextInterface $sc,
        ToolManager $toolManager
    )
    {
        $this->formFactory      = $formFactory;
        $this->homeTabManager   = $homeTabManager;
        $this->request          = $request;
        $this->widgetManager    = $widgetManager;
        $this->sc               = $sc;
        $this->toolManager      = $toolManager;
        $this->hometabAdminTool = $this->toolManager->getAdminToolByName('home_tabs');
    }

    /**
     * @EXT\Route(
     *     "/home_tabs/configuration/menu",
     *     name="claro_admin_home_tabs_configuration_menu",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabsConfigMenu.html.twig")
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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminDesktopHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @param integer $homeTabId
     *
     * @return array
     */
    public function adminDesktopHomeTabsConfigAction($homeTabId = -1)
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWorkspaceHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @param integer $homeTabId
     *
     * @return array
     */
    public function adminWorkspaceHomeTabsConfigAction($homeTabId = -1)
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabCreateForm.html.twig")
     *
     * Displays the admin homeTab form.
     *
     * @return Response
     */
    public function adminHomeTabCreateFormAction()
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabCreateForm.html.twig")
     *
     * Create a new admin homeTab.
     *
     * @param string $homeTabType
     *
     * @return array|Response
     */
    public function adminHomeTabCreateAction($homeTabType)
    {
        $this->checkOpen();

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
            } else {
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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabEditForm.html.twig")
     *
     * Displays the admin homeTab name edition form.
     *
     * @param HomeTab $homeTab
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminHomeTabEditFormAction(HomeTab $homeTab)
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabEditForm.html.twig")
     *
     * Edit the admin homeTab name.
     *
     * @param HomeTab $homeTab
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminHomeTabEditAction(HomeTab $homeTab)
    {
        $this->checkOpen();

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
     * @param HomeTabConfig $homeTabConfig
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminHomeTabConfigDeleteAction(HomeTabConfig $homeTabConfig)
    {
        $this->checkOpen();

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
     * @param HomeTabConfig $homeTabConfig
     * @param string        $visible
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminHomeTabUpdateVisibilityAction(
        HomeTabConfig $homeTabConfig,
        $visible
    )
    {
        $this->checkOpen();

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
     * @param HomeTabConfig $homeTabConfig
     * @param string        $locked
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminHomeTabUpdateLockAction(
        HomeTabConfig $homeTabConfig,
        $locked
    )
    {
        $this->checkOpen();

        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $isLocked = ($locked === 'locked') ? true : false;
        $this->homeTabManager->updateLock($homeTabConfig, $isLocked);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab_config/{homeTabConfigId}/change/order/{direction}",
     *     name="claro_admin_home_tab_config_change_order",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Change order of the given homeTabConfig in the given direction.
     *
     * @param HomeTabConfig $homeTabConfig
     * @param integer       $direction
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminHomeTabConfigChangeOrderAction(
        HomeTabConfig $homeTabConfig,
        $direction
    )
    {
        $this->checkOpen();

        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }

        $status = $this->homeTabManager->changeOrderHomeTabConfig(
            $homeTabConfig,
            $direction
        );

        return new Response($status, 200);
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
     * @param WidgetHomeTabConfig $widgetHomeTabConfig
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminWidgetHomeTabConfigDeleteAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceCreateForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @param string $homeTabType
     *
     * @return array
     */
    public function adminWidgetInstanceCreateFormAction($homeTabType)
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceCreateForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @param string $homeTabType
     *
     * @return Response
     */
    public function adminWidgetInstanceCreateAction($homeTabType)
    {
        $this->checkOpen();

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
     * @param HomeTab $homeTab
     * @param WidgetInstance $widgetInstance
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function associateWidgetToHomeTabAction(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance
    )
    {
        $this->checkOpen();

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
        } else {
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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceNameEditForm.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminWidgetInstanceNameFormAction(WidgetInstance $widgetInstance)
    {
        $this->checkOpen();

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
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceNameEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     */
    public function adminWidgetInstanceNameAction(WidgetInstance $widgetInstance)
    {
        $this->checkOpen();

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
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig $widgetHomeTabConfig
     * @param string $direction
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return Response
     */
    public function adminWidgetHomeTabConfigChangeOrderAction(
        WidgetHomeTabConfig $widgetHomeTabConfig,
        $direction
    )
    {
        $this->checkOpen();

        if (!is_null($widgetHomeTabConfig->getUser()) ||
            !is_null($widgetHomeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }

        $status = $this->homeTabManager->changeOrderWidgetHomeTabConfig(
            $widgetHomeTabConfig,
            $direction
        );

        return new Response($status, 200);
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
     * @param WidgetHomeTabConfig $widgetHomeTabConfig
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminWidgetHomeTabConfigChangeVisibilityAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkOpen();

        if (!is_null($widgetHomeTabConfig->getUser()) ||
            !is_null($widgetHomeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $this->homeTabManager->changeVisibilityWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

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
     * @param WidgetHomeTabConfig $widgetHomeTabConfig
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminWidgetHomeTabConfigChangeLockAction(
        WidgetHomeTabConfig $widgetHomeTabConfig
    )
    {
        $this->checkOpen();

        if (!is_null($widgetHomeTabConfig->getUser()) ||
            !is_null($widgetHomeTabConfig->getWorkspace())) {

            throw new AccessDeniedException();
        }
        $this->homeTabManager->changeLockWidgetHomeTabConfig(
            $widgetHomeTabConfig
        );

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/widget/{widgetInstance}/form",
     *     name="claro_admin_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to render its configuration page.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function getAdminWidgetFormConfigurationAction(
        WidgetInstance $widgetInstance
    )
    {
        $this->checkOpen();

        if (!is_null($widgetInstance->getUser()) ||
            !is_null($widgetInstance->getWorkspace())) {

            throw new AccessDeniedException();
        }

        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->hometabAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
