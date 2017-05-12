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
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabAdminCreateEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabAdminDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabAdminEditEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetAdminCreateEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetAdminDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetAdminEditEvent;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\WidgetInstanceConfigType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('desktop_and_home')")
 */
class HomeTabController extends Controller
{
    private $apiManager;
    private $bundles;
    private $eventDispatcher;
    private $homeTabManager;
    private $pluginManager;
    private $request;
    private $serializer;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "pluginManager"   = @DI\Inject("claroline.manager.plugin_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "serializer"      = @DI\Inject("jms_serializer"),
     *     "widgetManager"   = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        EventDispatcherInterface $eventDispatcher,
        HomeTabManager $homeTabManager,
        PluginManager $pluginManager,
        Request $request,
        Serializer $serializer,
        WidgetManager $widgetManager
    ) {
        $this->apiManager = $apiManager;
        $this->bundles = $pluginManager->getEnabled(true);
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->pluginManager = $pluginManager;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/desktop/hometabs/configuration",
     *     name="claro_admin_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\HomeTab:adminHomeTabsConfig.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @return array
     */
    public function adminHomeTabsConfigAction()
    {
        return [];
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tabs",
     *     name="api_get_admin_home_tabs",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns list of admin home tabs
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAdminHomeTabsAction()
    {
        $datas = [];
        $hometabConfigs = $this->homeTabManager->getAdminDesktopHomeTabConfigs();

        foreach ($hometabConfigs as $htc) {
            $datas[] = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/create/form",
     *     name="api_get_admin_home_tab_creation_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the home tab creation form
     */
    public function getAdminHomeTabCreationFormAction()
    {
        $formType = new HomeTabType('admin');
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\adminHomeTabCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/type/{homeTabType}/create",
     *     name="api_post_admin_home_tab_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a desktop home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAdminHomeTabCreationAction($homeTabType = 'desktop')
    {
        $isDesktop = ($homeTabType === 'desktop');
        $type = $isDesktop ? 'admin_desktop' : 'admin_workspace';
        $formType = new HomeTabType('admin');
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $color = $form->get('color')->getData();
            $locked = $form->get('locked')->getData();
            $visible = $form->get('visible')->getData();
            $roles = $formDatas['roles'];

            $homeTab = new HomeTab();
            $homeTab->setName($formDatas['name']);
            $homeTab->setType($type);

            foreach ($roles as $role) {
                $homeTab->addRole($role);
            }
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType($type);
            $homeTabConfig->setLocked($locked);
            $homeTabConfig->setVisible($visible);
            $homeTabConfig->setDetails(['color' => $color]);
            $lastOrder = $isDesktop ?
                $this->homeTabManager->getOrderOfLastAdminDesktopHomeTabConfig() :
                $this->homeTabManager->getOrderOfLastAdminWorkspaceHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabAdminCreateEvent($homeTabConfig);
            $this->eventDispatcher->dispatch('log', $event);

            return new JsonResponse(
                $this->serializer->serialize($homeTabConfig, 'json', SerializationContext::create()->setGroups(['api_home_tab'])),
                200
            );
        } else {
            $options = [
                'http_code' => 400,
                'extra_parameters' => null,
                'serializer_group' => 'api_home_tab',
            ];

            return $this->apiManager->handleFormView(
                'ClarolineCoreBundle:API:HomeTab\adminHomeTabCreateForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/{homeTabConfig}/type/{homeTabType}/edit/form",
     *     name="api_get_admin_home_tab_edition_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the admin home tab edition form
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAdminHomeTabEditionFormAction(HomeTabConfig $homeTabConfig, $homeTabType = 'desktop')
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);
        $visible = $homeTabConfig->isVisible();
        $locked = $homeTabConfig->isLocked();
        $details = $homeTabConfig->getDetails();
        $color = isset($details['color']) ? $details['color'] : null;
        $formType = new HomeTabType('admin', $color, $locked, $visible);
        $formType->enableApi();
        $form = $this->createForm($formType, $homeTab);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\adminHomeTabEditForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/{homeTabConfig}/type/{homeTabType}/edit",
     *     name="api_put_admin_home_tab_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Edits an admin home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putAdminHomeTabEditionAction(HomeTabConfig $homeTabConfig, $homeTabType = 'desktop')
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);
        $formType = new HomeTabType('admin');
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $color = $form->get('color')->getData();
            $locked = $form->get('locked')->getData();
            $visible = $form->get('visible')->getData();
            $roles = $formDatas['roles'];
            $homeTab->emptyRoles();

            foreach ($roles as $role) {
                $homeTab->addRole($role);
            }
            $homeTab->setName($formDatas['name']);
            $homeTabConfig->setVisible($visible);
            $homeTabConfig->setLocked($locked);
            $details = $homeTabConfig->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['color'] = $color;
            $homeTabConfig->setDetails($details);
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabAdminEditEvent($homeTabConfig);
            $this->eventDispatcher->dispatch('log', $event);

            return new JsonResponse(
                $this->serializer->serialize($homeTabConfig, 'json', SerializationContext::create()->setGroups(['api_home_tab'])),
                200
            );
        } else {
            $options = [
                'http_code' => 400,
                'extra_parameters' => null,
                'serializer_group' => 'api_home_tab',
            ];

            return $this->apiManager->handleFormView(
                'ClarolineCoreBundle:API:HomeTab\adminHomeTabEditForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/{homeTabConfig}/type/{homeTabType}/delete",
     *     name="api_delete_admin_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Deletes admin home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAdminHomeTabAction(HomeTabConfig $homeTabConfig, $homeTabType = 'desktop')
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);
        $htcDatas = $this->serializer->serialize($homeTabConfig, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        $this->homeTabManager->deleteHomeTabConfig($homeTabConfig);
        $this->homeTabManager->deleteHomeTab($homeTab);
        $event = new LogHomeTabAdminDeleteEvent(json_decode($htcDatas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($htcDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/admin/type/{homeTabType}/home/tab/{homeTabConfig}/next/{nextHomeTabConfigId}/reorder",
     *     name="api_post_admin_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Update admin HomeTabConfig order
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAdminHomeTabConfigReorderAction($homeTabType, HomeTabConfig $homeTabConfig, $nextHomeTabConfigId)
    {
        $this->checkAdminHomeTabConfig($homeTabConfig, $homeTabType);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkAdminHomeTab($homeTab, $homeTabType);

        $this->homeTabManager->reorderAdminHomeTabConfigs($homeTabType, $homeTabConfig, $nextHomeTabConfigId);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/{homeTab}/widgets/display",
     *     name="api_get_admin_widgets_display",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Retrieves admin widgets
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAdminWidgetsAction(HomeTab $homeTab)
    {
        $widgets = [];
        $configs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);
        $wdcs = $this->widgetManager->generateWidgetDisplayConfigsForAdmin($configs);

        foreach ($configs as $config) {
            $widgetDatas = [];
            $widgetInstance = $config->getWidgetInstance();
            $widget = $widgetInstance->getWidget();
            $widgetInstanceId = $widgetInstance->getId();
            $widgetDatas['config'] = $this->serializer->serialize($config, 'json', SerializationContext::create()->setGroups(['api_widget']));
            $widgetDatas['display'] = $this->serializer->serialize($wdcs[$widgetInstanceId], 'json', SerializationContext::create()->setGroups(['api_widget']));
            $widgetDatas['configurable'] = $widget->isConfigurable();
            $event = $this->eventDispatcher->dispatch('widget_'.$widget->getName(), new DisplayWidgetEvent($widgetInstance));
            $widgetDatas['content'] = $event->getContent();
            $widgets[] = $widgetDatas;
        }

        return new JsonResponse($widgets, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/widget/create/form",
     *     name="api_get_admin_widget_instance_creation_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the widget instance creation form
     */
    public function getAdminInstanceCreationFormAction()
    {
        $formType = new WidgetInstanceConfigType('admin', $this->bundles);
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Widget\widgetInstanceCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/{homeTab}/type/{homeTabType}/widget/create",
     *     name="api_post_admin_widget_instance_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a new widget instance
     */
    public function postAdminWidgetInstanceCreationAction(HomeTab $homeTab, $homeTabType = 'desktop')
    {
        $this->checkAdminHomeTab($homeTab, $homeTabType);
        $isDesktop = ($homeTabType === 'desktop');
        $formType = new WidgetInstanceConfigType('admin', $this->bundles);
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $widget = $formDatas['widget'];
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();
            $locked = $form->get('locked')->getData();
            $visible = $form->get('visible')->getData();

            $widgetInstance = new WidgetInstance();
            $widgetHomeTabConfig = new WidgetHomeTabConfig();
            $widgetDisplayConfig = new WidgetDisplayConfig();
            $widgetInstance->setName($formDatas['name']);
            $widgetInstance->setWidget($widget);
            $widgetInstance->setIsAdmin(true);
            $widgetInstance->setIsDesktop($isDesktop);
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setVisible($visible);
            $widgetHomeTabConfig->setLocked($locked);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('admin');
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());
            $widgetDisplayConfig->setColor($color);
            $widgetDisplayConfig->setDetails(['textTitleColor' => $textTitleColor]);
            $this->widgetManager->persistWidgetConfigs($widgetInstance, $widgetHomeTabConfig, $widgetDisplayConfig);
            $event = new LogWidgetAdminCreateEvent($homeTab, $widgetHomeTabConfig, $widgetDisplayConfig);
            $this->eventDispatcher->dispatch('log', $event);
            $widgetDatas = [
                'config' => $this->serializer->serialize($widgetHomeTabConfig, 'json', SerializationContext::create()->setGroups(['api_widget'])),
                'display' => $this->serializer->serialize($widgetDisplayConfig, 'json', SerializationContext::create()->setGroups(['api_widget'])),
                'configurable' => $widget->isConfigurable(),
            ];

            return new JsonResponse($widgetDatas, 200);
        } else {
            $options = [
                'http_code' => 400,
                'extra_parameters' => null,
                'serializer_group' => 'api_widget',
            ];

            return $this->apiManager->handleFormView(
                'ClarolineCoreBundle:API:Widget\widgetInstanceCreateForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/widget/config/{whtc}/display/{wdc}/edit/form",
     *     name="api_get_admin_widget_instance_edition_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the widget instance edition form
     */
    public function getAdminWidgetInstanceEditionFormAction(WidgetHomeTabConfig $whtc, WidgetDisplayConfig $wdc)
    {
        $this->checkAdminAccessForWidgetHomeTabConfig($whtc);
        $this->checkAdminAccessForWidgetDisplayConfig($wdc);
        $widgetInstance = $wdc->getWidgetInstance();
        $widget = $widgetInstance->getWidget();
        $this->checkAdminAccessForWidgetInstance($widgetInstance);
        $visible = $whtc->isVisible();
        $locked = $whtc->isLocked();
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('admin', $this->bundles, false, [], $color, $textTitleColor, $locked, $visible, false);
        $formType->enableApi();
        $form = $this->createForm($formType, $widgetInstance);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Widget\widgetInstanceEditForm.html.twig',
            $form,
            ['extra_infos' => $widget->isConfigurable(), 'form_view' => ['instance' => $widgetInstance]]
        );
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/widget/config/{whtc}/display/{wdc}/edit",
     *     name="api_put_admin_widget_instance_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Edits widget instance config
     */
    public function putAdminWidgetInstanceEditionAction(WidgetHomeTabConfig $whtc, WidgetDisplayConfig $wdc)
    {
        $widgetInstance = $wdc->getWidgetInstance();
        $widget = $widgetInstance->getWidget();
        $this->checkAdminAccessForWidgetHomeTabConfig($whtc);
        $this->checkAdminAccessForWidgetDisplayConfig($wdc);
        $this->checkAdminAccessForWidgetInstance($widgetInstance);
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $visible = $whtc->isVisible();
        $locked = $whtc->isLocked();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('admin', $this->bundles, false, [], $color, $textTitleColor, $locked, $visible, false);
        $formType->enableApi();
        $form = $this->createForm($formType, $widgetInstance);
        $form->submit($this->request);

        if ($form->isValid()) {
            $instance = $form->getData();
            $name = $instance->getName();
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();
            $visible = $form->get('visible')->getData();
            $locked = $form->get('locked')->getData();
            $widgetInstance->setName($name);
            $whtc->setVisible($visible);
            $whtc->setLocked($locked);
            $wdc->setColor($color);
            $details = $wdc->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['textTitleColor'] = $textTitleColor;
            $wdc->setDetails($details);

            $this->widgetManager->persistWidgetConfigs($widgetInstance, null, $wdc);
            $event = new LogWidgetAdminEditEvent($widgetInstance, $whtc, $wdc);
            $this->eventDispatcher->dispatch('log', $event);
            $widgetDatas = [
                'config' => $this->serializer->serialize($whtc, 'json', SerializationContext::create()->setGroups(['api_widget'])),
                'display' => $this->serializer->serialize($wdc, 'json', SerializationContext::create()->setGroups(['api_widget'])),
            ];

            return new JsonResponse($widgetDatas, 200);
        } else {
            $options = [
                'http_code' => 400,
                'extra_parameters' => null,
                'serializer_group' => 'api_widget',
                'extra_infos' => $widget->isConfigurable(),
                'form_view' => ['instance' => $widgetInstance],
            ];

            return $this->apiManager->handleFormView(
                'ClarolineCoreBundle:API:Widget\widgetInstanceEditForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/admin/home/tab/widget/{widgetHomeTabConfig}/delete",
     *     name="api_delete_admin_widget_home_tab_config",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Deletes a widget
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAdminWidgetHomeTabConfigAction(WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $this->checkAdminAccessForWidgetHomeTabConfig($widgetHomeTabConfig);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
        $datas = $this->serializer->serialize($widgetInstance, 'json', SerializationContext::create()->setGroups(['api_widget']));
        $this->homeTabManager->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
        $this->widgetManager->removeInstance($widgetInstance);
        $event = new LogWidgetAdminDeleteEvent(json_decode($datas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/admin/widget/display/{datas}/update",
     *     name="api_put_admin_widget_display_update",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Updates widgets display
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putAdminWidgetDisplayUpdateAction($datas)
    {
        $jsonDatas = json_decode($datas, true);
        $displayConfigs = [];

        foreach ($jsonDatas as $data) {
            $displayConfig = $this->widgetManager->getWidgetDisplayConfigById($data['id']);

            if (!is_null($displayConfig)) {
                $this->checkAdminAccessForWidgetDisplayConfig($displayConfig);
                $displayConfig->setRow($data['row']);
                $displayConfig->setColumn($data['col']);
                $displayConfig->setWidth($data['sizeX']);
                $displayConfig->setHeight($data['sizeY']);
                $displayConfigs[] = $displayConfig;
            }
        }
        $this->widgetManager->persistWidgetDisplayConfigs($displayConfigs);

        return new JsonResponse($jsonDatas, 200);
    }

    private function checkAdminHomeTab(HomeTab $homeTab, $homeTabType)
    {
        if (!is_null($homeTab->getUser()) ||
            !is_null($homeTab->getWorkspace()) ||
            $homeTab->getType() !== 'admin_'.$homeTabType) {
            throw new AccessDeniedException();
        }
    }

    private function checkAdminHomeTabConfig(HomeTabConfig $homeTabConfig, $homeTabType)
    {
        if (!is_null($homeTabConfig->getUser()) ||
            !is_null($homeTabConfig->getWorkspace()) ||
            $homeTabConfig->getType() !== 'admin_'.$homeTabType) {
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
