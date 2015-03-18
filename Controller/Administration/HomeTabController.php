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
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\HomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetDisplayType;
use Claroline\CoreBundle\Form\WidgetDisplayConfigType;
use Claroline\CoreBundle\Form\WidgetHomeTabConfigType;
use Claroline\CoreBundle\Form\WidgetInstanceType;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;

class HomeTabController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $widgetManager;
    private $sc;
    private $hometabAdminTool;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "widgetManager"   = @DI\Inject("claroline.manager.widget_manager"),
     *     "sc"              = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        WidgetManager $widgetManager,
        SecurityContextInterface $sc,
        ToolManager $toolManager
    )
    {
        $this->eventDispatcher  = $eventDispatcher;
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
        $this->checkOpen();

        $homeTabConfigs = ($homeTabType === 'desktop') ?
            $this->homeTabManager->getAdminDesktopHomeTabConfigs() :
            $this->homeTabManager->getAdminWorkspaceHomeTabConfigs();
        $tabId = intval($homeTabId);
        $widgets = array();
        $firstElement = true;

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
            'widgetsDatas' => $widgets
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
        $this->checkOpen();

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
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
        $this->checkOpen();

        $isDesktop = ($homeTabType === 'desktop');
        $type = $isDesktop ? 'admin_desktop' : 'admin_workspace';

        $homeTab = new HomeTab();
        $homeTabConfig = new HomeTabConfig();
        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
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
        $this->checkOpen();
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
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
        $this->checkOpen();
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);

        $homeTabForm = $this->formFactory->create(
            new HomeTabType(),
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
        $this->checkOpen();
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
}
