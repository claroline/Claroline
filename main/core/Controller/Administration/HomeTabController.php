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
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\HomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetDisplayType;
use Claroline\CoreBundle\Form\WidgetDisplayConfigType;
use Claroline\CoreBundle\Form\WidgetHomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetInstanceType;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('home_tabs')")
 */
class HomeTabController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "widgetManager"   = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        WidgetManager $widgetManager
    )
    {
        $this->eventDispatcher  = $eventDispatcher;
        $this->formFactory      = $formFactory;
        $this->homeTabManager   = $homeTabManager;
        $this->request          = $request;
        $this->widgetManager    = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/home_tabs/configuration/menu",
     *     name="claro_admin_home_tabs_configuration_menu",
     *     options = {"expose"=true}
     * )
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
     *     "/home_tabs/{homeTabId}/type/{homeTabType}/configuration",
     *     name="claro_admin_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @param integer $homeTabId
     *
     * @return array
     */
    public function adminHomeTabsConfigAction($homeTabType, $homeTabId = -1)
    {
        $homeTabConfigs = ($homeTabType === 'desktop') ?
            $this->homeTabManager->getAdminDesktopHomeTabConfigs() :
            $this->homeTabManager->getAdminWorkspaceHomeTabConfigs();
        $tabId = intval($homeTabId);
        $widgets = array();
        $firstElement = true;
        $initWidgetsPosition = false;

        if ($tabId !== -1) {
            foreach ($homeTabConfigs as $homeTabConfig) {
                if ($tabId === $homeTabConfig->getHomeTab()->getId()) {
                    $firstElement = false;
                    break;
                }
            }
        }

        if ($firstElement) {
            $firstHomeTabConfig = reset($homeTabConfigs);

            if ($firstHomeTabConfig) {
                $tabId = $firstHomeTabConfig->getHomeTab()->getId();
            }
        }
        $homeTab = $this->homeTabManager->getAdminHomeTabByIdAndType($tabId, $homeTabType);
        $widgetHomeTabConfigs = is_null($homeTab) ?
            array() :
            $this->homeTabManager->getAdminWidgetConfigs($homeTab);
        $wdcs = $this->widgetManager->generateWidgetDisplayConfigsForAdmin($widgetHomeTabConfigs);

        foreach ($wdcs as $wdc) {

            if ($wdc->getRow() === -1 || $wdc->getColumn() === -1) {
                $initWidgetsPosition = true;
                break;
            }
        }

        foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
            $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

            $event = $this->eventDispatcher->dispatch(
                "widget_{$widgetInstance->getWidget()->getName()}",
                'DisplayWidget',
                array($widgetInstance)
            );

            $widget['config'] = $widgetHomeTabConfig;
            $widget['content'] = $event->getContent();
            $widgetInstanceId = $widgetHomeTabConfig->getWidgetInstance()->getId();
            $widget['widgetDisplayConfig'] = $wdcs[$widgetInstanceId];
            $widgets[] = $widget;
        }

        return array(
            'curentHomeTabId' => $tabId,
            'homeTabType' => $homeTabType,
            'homeTabConfigs' => $homeTabConfigs,
            'widgetsDatas' => $widgets,
            'initWidgetsPosition' => $initWidgetsPosition
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/type/{homeTabType}/create/form",
     *     name="claro_admin_home_tab_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabCreateModalForm.html.twig")
     *
     * Displays the admin homeTab form.
     *
     * @return Response
     */
    public function adminHomeTabCreateFormAction($homeTabType)
    {
        $homeTabForm = $this->formFactory->create(
            new HomeTabType(null, true),
            new HomeTab()
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(true),
            new HomeTabConfig()
        );

        return array(
            'homeTabType' => $homeTabType,
            'homeTabForm' => $homeTabForm->createView(),
            'homeTabConfigForm' => $homeTabConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/type/{homeTabType}/create",
     *     name="claro_admin_home_tab_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabCreateModalForm.html.twig")
     *
     * Create a new admin homeTab.
     *
     * @param string $homeTabType
     *
     * @return array|Response
     */
    public function adminHomeTabCreateAction($homeTabType)
    {
        $isDesktop = ($homeTabType === 'desktop');
        $type = $isDesktop ? 'admin_desktop' : 'admin_workspace';

        $homeTab = new HomeTab();
        $homeTabConfig = new HomeTabConfig();
        $homeTabForm = $this->formFactory->create(
            new HomeTabType(null, true),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(true),
            $homeTabConfig
        );
        $homeTabForm->handleRequest($this->request);
        $homeTabConfigForm->handleRequest($this->request);

        if ($homeTabForm->isValid() && $homeTabConfigForm->isValid()) {
            $homeTab->setType($type);
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType($type);

            $lastOrder = $isDesktop ?
                $this->homeTabManager->getOrderOfLastAdminDesktopHomeTabConfig() :
                $this->homeTabManager->getOrderOfLastAdminWorkspaceHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);

            return new JsonResponse($homeTab->getId(), 200);
        } else {

            return array(
                'homeTabType' => $homeTabType,
                'homeTabForm' => $homeTabForm->createView(),
                'homeTabConfigForm' => $homeTabConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTab}/type/{homeTabType}/config/{homeTabConfig}/edit/form",
     *     name="claro_admin_home_tab_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabEditModalForm.html.twig")
     *
     * Displays the admin homeTab name edition form.
     *
     * @param HomeTab $homeTab
     * @param HomeTabConfig $homeTabConfig
     * @param string $homeTabType
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminHomeTabEditFormAction(
        HomeTab $homeTab,
        HomeTabConfig $homeTabConfig,
        $homeTabType
    )
    {
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(null, true),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(true),
            $homeTabConfig
        );

        return array(
            'homeTab' => $homeTab,
            'homeTabConfig' => $homeTabConfig,
            'homeTabType' => $homeTabType,
            'homeTabForm' => $homeTabForm->createView(),
            'homeTabConfigForm' => $homeTabConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTab}/type/{homeTabType}/config/{homeTabConfig}/edit",
     *     name="claro_admin_home_tab_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabEditModalForm.html.twig")
     *
     * Edit the admin homeTab name.
     *
     * @param HomeTab $homeTab
     * @param HomeTabConfig $homeTabConfig
     * @param string $homeTabType
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminHomeTabEditAction(
        HomeTab $homeTab,
        HomeTabConfig $homeTabConfig,
        $homeTabType
    )
    {
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(null, true),
            $homeTab
        );
        $homeTabConfigForm = $this->formFactory->create(
            new HomeTabConfigType(true),
            $homeTabConfig
        );
        $homeTabForm->handleRequest($this->request);
        $homeTabConfigForm->handleRequest($this->request);

        if ($homeTabForm->isValid() && $homeTabConfigForm->isValid()) {
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $visibility = $homeTabConfig->isVisible() ? 'visible' : 'hidden';
            $lock = $homeTabConfig->isLocked() ? 'locked' : 'unlocked';

            return new JsonResponse(
                array(
                    'id' => $homeTab->getId(),
                    'name' => $homeTab->getName(),
                    'visibility' => $visibility,
                    'lock' => $lock
                ),
                200
            );
        } else {

            return array(
                'homeTab' => $homeTab,
                'homeTabConfig' => $homeTabConfig,
                'homeTabType' => $homeTabType,
                'homeTabForm' => $homeTabForm->createView(),
                'homeTabConfigForm' => $homeTabConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTab}/type/{homeTabType}/delete",
     *     name="claro_admin_home_tab_delete",
     *     options = {"expose"=true}
     * )
     *
     * Delete the given homeTab.
     *
     * @param HomeTab $homeTab
     * @param string $homeTabType
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminHomeTabDeleteAction(
        HomeTab $homeTab,
        $homeTabType
    )
    {
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->homeTabManager->deleteHomeTab($homeTab);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "home_tab/type/{homeTabType}/config/{homeTabConfig}/reorder/next/{nextHomeTabConfigId}",
     *     name="claro_admin_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Update workspace HomeTabConfig order
     *
     * @return Response
     */
    public function adminHomeTabConfigReorderAction(
        $homeTabType,
        HomeTabConfig $homeTabConfig,
        $nextHomeTabConfigId
    )
    {
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkAdminHomeTab($homeTab, $homeTabType);

        $this->homeTabManager->reorderAdminHomeTabConfigs(
            $homeTabType,
            $homeTabConfig,
            $nextHomeTabConfigId
        );

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/widget_home_tab_config/{widgetHomeTabConfig}/delete",
     *     name="claro_admin_widget_home_tab_config_delete",
     *     options = {"expose"=true}
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
        $this->checkAdminAccessForWidgetHomeTabConfig($widgetHomeTabConfig);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
        $this->homeTabManager->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
        $this->widgetManager->removeInstance($widgetInstance);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/widget/diplay/config/{widgetDisplayConfig}/position/row/{row}/column/{column}/update",
     *     name="claro_admin_widget_display_config_position_update",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Update widget position.
     *
     * @return Response
     */
    public function adminWidgetDisplayConfigPositionUpdateAction(
        WidgetDisplayConfig $widgetDisplayConfig,
        $row,
        $column
    )
    {
        $this->checkAdminAccessForWidgetDisplayConfig($widgetDisplayConfig);
        $widgetDisplayConfig->setRow($row);
        $widgetDisplayConfig->setColumn($column);
        $this->widgetManager->persistWidgetDisplayConfigs(array($widgetDisplayConfig));

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "hometab/{homeTab}/type/{homeTabType}/widget/instance/create/form",
     *     name="claro_admin_widget_instance_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceCreateModalForm.html.twig")
     *
     * Displays the widget instance form.
     *
     * @param HomeTab $homeTab
     * @param string $homeTabType
     *
     * @return array
     */
    public function adminWidgetInstanceCreateFormAction(HomeTab $homeTab, $homeTabType)
    {
        $isDesktop = ($homeTabType === 'desktop');
        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType($isDesktop),
            new WidgetInstance()
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(true),
            new WidgetHomeTabConfig()
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            new WidgetDisplayConfig()
        );

        return array(
            'homeTabType' => $homeTabType,
            'homeTab' => $homeTab,
            'instanceForm' => $instanceForm->createView(),
            'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
            'displayConfigForm' => $displayConfigForm->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "hometab/{homeTab}/type/{homeTabType}/widget/instance/create",
     *     name="claro_admin_widget_instance_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetInstanceCreateModalForm.html.twig")
     *
     * Creates a widget instance.
     *
     * @param HomeTab $homeTab
     * @param string $homeTabType
     *
     * @return Response
     */
    public function adminWidgetInstanceCreateAction(HomeTab $homeTab, $homeTabType)
    {
        $isDesktop = ($homeTabType === 'desktop');
        $widgetInstance = new WidgetInstance();
        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetDisplayConfig = new WidgetDisplayConfig();

        $instanceForm = $this->formFactory->create(
            new WidgetInstanceType($isDesktop),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(true),
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

            $widgetInstance->setIsAdmin(true);
            $widgetInstance->setIsDesktop($isDesktop);
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('admin');
            $widget = $widgetInstance->getWidget();
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );

            return new JsonResponse(
                array(
                    'widgetInstanceId' => $widgetInstance->getId(),
                    'widgetHomeTabConfigId' => $widgetHomeTabConfig->getId(),
                    'widgetDisplayConfigId' => $widgetDisplayConfig->getId(),
                    'color' => $widgetDisplayConfig->getColor(),
                    'name' => $widgetInstance->getName(),
                    'configurable' => $widgetInstance->getWidget()->isConfigurable() ? 1 : 0,
                    'visibility' => $widgetHomeTabConfig->isVisible() ? 1 : 0,
                    'lock' => $widgetHomeTabConfig->isLocked() ? 1 : 0,
                    'width' => $widget->getDefaultWidth(),
                    'height' => $widget->getDefaultHeight()
                ),
                200
            );
        } else {

            return array(
                'homeTabType' => $homeTabType,
                'homeTab' => $homeTab,
                'instanceForm' => $instanceForm->createView(),
                'widgetHomeTabConfigForm' => $widgetHomeTabConfigForm->createView(),
                'displayConfigForm' => $displayConfigForm->createView()
            );
        }
    }

    /**
     * @EXT\Route(
     *     "home_tab/type/{homeTabType}/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit/form",
     *     name = "claro_admin_widget_config_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetConfigEditModalForm.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     * @param WidgetHomeTabConfig $widgetHomeTabConfig
     * @param WidgetDisplayConfig $widgetDisplayConfig
     * @param string $homeTabType
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function adminWidgetConfigEditFormAction(
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig,
        $homeTabType
    )
    {
        $this->checkAdminAccessForWidgetInstance($widgetInstance);
        $this->checkAdminAccessForWidgetHomeTabConfig($widgetHomeTabConfig);
        $this->checkAdminAccessForWidgetDisplayConfig($widgetDisplayConfig);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(true),
            $widgetHomeTabConfig
        );
        $displayConfigForm = $this->formFactory->create(
            new WidgetDisplayConfigType(),
            $widgetDisplayConfig
        );

        return array(
            'homeTabType' => $homeTabType,
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
     *     "home_tab/type/{homeTabType}/widget/instance/{widgetInstance}/config/{widgetHomeTabConfig}/display/{widgetDisplayConfig}/edit",
     *     name = "claro_admin_widget_config_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminWidgetConfigEditModalForm.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     * @param WidgetHomeTabConfig $widgetHomeTabConfig
     * @param WidgetDisplayConfig $widgetDisplayConfig
     * @param string $homeTabType
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     */
    public function adminWidgetConfigEditAction(
        WidgetInstance $widgetInstance,
        WidgetHomeTabConfig $widgetHomeTabConfig,
        WidgetDisplayConfig $widgetDisplayConfig,
        $homeTabType
    )
    {
        $this->checkAdminAccessForWidgetInstance($widgetInstance);
        $this->checkAdminAccessForWidgetHomeTabConfig($widgetHomeTabConfig);
        $this->checkAdminAccessForWidgetDisplayConfig($widgetDisplayConfig);

        $instanceForm = $this->formFactory->create(
            new WidgetDisplayType(),
            $widgetInstance
        );
        $widgetHomeTabConfigForm = $this->formFactory->create(
            new WidgetHomeTabConfigType(true),
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
            $lock = $widgetHomeTabConfig->isLocked() ?
                'locked' :
                'unlocked';

            return new JsonResponse(
                array(
                    'id' => $widgetHomeTabConfig->getId(),
                    'color' => $widgetDisplayConfig->getColor(),
                    'title' => $widgetInstance->getName(),
                    'visibility' => $visibility,
                    'lock' => $lock
                ),
                200
            );
        } else {

            return array(
                'homeTabType' => $homeTabType,
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
     *     "/widget/{widgetInstance}/form",
     *     name="claro_admin_widget_configuration",
     *     options={"expose"=true}
     * )
     *
     * Asks a widget to render its configuration page.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function getAdminWidgetFormConfigurationAction(WidgetInstance $widgetInstance)
    {
        $this->checkAdminAccessForWidgetInstance($widgetInstance);

        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "update/widgets/display/config",
     *     name="claro_admin_update_widgets_display_config",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "widgetDisplayConfigs",
     *      class="ClarolineCoreBundle:Widget\WidgetDisplayConfig",
     *      options={"multipleIds" = true, "name" = "wdcIds"}
     * )
     */
    public function updateAdminWidgetsDisplayConfigAction(array $widgetDisplayConfigs)
    {
        $toPersist = array();

        foreach ($widgetDisplayConfigs as $config) {

            $this->checkAdminAccessForWidgetDisplayConfig($config);
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

    private function checkAdminHomeTab(HomeTab $homeTab, $homeTabType)
    {
        if (!is_null($homeTab->getUser()) ||
            !is_null($homeTab->getWorkspace()) ||
            $homeTab->getType() !== 'admin_' . $homeTabType) {

            throw new AccessDeniedException();
        }
    }

    private function checkAdminHomeTabConfig(
        HomeTabConfig $homeTabConfig,
        $homeTabType
    )
    {
        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace()) ||
            $homeTabConfig->getType() !== 'admin_' . $homeTabType) {

            throw new AccessDeniedException();
        }
    }

    private function checkAdminAccessForWidgetInstance(WidgetInstance $widgetInstance)
    {
        if (!is_null($widgetInstance->getUser()) ||
            !is_null($widgetInstance->getWorkspace())) {

            throw new AccessDeniedException();
        }
    }

    private function checkAdminAccessForWidgetHomeTabConfig(WidgetHomeTabConfig $whtc)
    {
        if ($whtc->getType() !== 'admin' ||
            !is_null($whtc->getUser()) ||
            !is_null($whtc->getWorkspace())) {

            throw new AccessDeniedException();
        }
    }

    private function checkAdminAccessForWidgetDisplayConfig(WidgetDisplayConfig $wdc)
    {
        if (!is_null($wdc->getUser()) || !is_null($wdc->getWorkspace())) {

            throw new AccessDeniedException();
        }
    }
}
