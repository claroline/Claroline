<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Workspace;

use Claroline\CoreBundle\Controller\Exception\WorkspaceAccessDeniedException;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabWorkspaceCreateEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabWorkspaceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabWorkspaceEditEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabWorkspacePinEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetWorkspaceCreateEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetWorkspaceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetWorkspaceEditEvent;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\WidgetInstanceConfigType;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceHomeController extends Controller
{
    private $apiManager;
    private $authorization;
    private $bundles;
    private $eventDispatcher;
    private $homeTabManager;
    private $pluginManager;
    private $request;
    private $serializer;
    private $tokenStorage;
    private $utils;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "pluginManager"   = @DI\Inject("claroline.manager.plugin_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "serializer"      = @DI\Inject("jms_serializer"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "utils"           = @DI\Inject("claroline.security.utilities"),
     *     "widgetManager"   = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        HomeTabManager $homeTabManager,
        PluginManager $pluginManager,
        Request $request,
        Serializer $serializer,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        WidgetManager $widgetManager
    ) {
        $this->apiManager = $apiManager;
        $this->authorization = $authorization;
        $this->bundles = $pluginManager->getEnabled(true);
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->pluginManager = $pluginManager;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/{workspace}/home/tabs",
     *     name="api_get_workspace_home_tabs",
     *     options = {"expose"=true}
     * )
     *
     * Returns list of workspace home tabs
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getWorkspaceHomeTabsAction(Workspace $workspace)
    {
        $this->checkToolAccess($workspace);
        $datas = [];
        $canEdit = $this->hasWorkspaceEditionAccess($workspace);
        $roleNames = $this->utils->getRoles($this->tokenStorage->getToken());
        $hometabConfigs = $canEdit ?
            $this->homeTabManager->getWorkspaceHomeTabConfigsByWorkspace($workspace) :
            $this->homeTabManager->getVisibleWorkspaceHomeTabConfigsByWorkspaceAndRoles($workspace, $roleNames);

        foreach ($hometabConfigs as $htc) {
            $datas[] = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/{workspace}/home/tab/create/form",
     *     name="api_get_workspace_home_tab_creation_form",
     *     options = {"expose"=true}
     * )
     *
     * Returns the home tab creation form
     */
    public function getWorkspaceHomeTabCreationFormAction(Workspace $workspace)
    {
        $this->checkToolEditionAccess($workspace);
        $formType = new HomeTabType('workspace', null, false, true, $workspace);
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\workspaceHomeTabCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/{workspace}/home/tab/create",
     *     name="api_post_workspace_home_tab_creation",
     *     options = {"expose"=true}
     * )
     *
     * Creates a desktop home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postWorkspaceHomeTabCreationAction(Workspace $workspace)
    {
        $this->checkToolEditionAccess($workspace);
        $formType = new HomeTabType('workspace', null, false, true, $workspace);
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $color = $form->get('color')->getData();
            $visible = $form->get('visible')->getData();
            $roles = $formDatas['roles'];

            $homeTab = new HomeTab();
            $homeTab->setName($formDatas['name']);
            $homeTab->setType('workspace');
            $homeTab->setWorkspace($workspace);

            foreach ($roles as $role) {
                $homeTab->addRole($role);
            }
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('workspace');
            $homeTabConfig->setWorkspace($workspace);
            $homeTabConfig->setVisible($visible);
            $homeTabConfig->setDetails(['color' => $color]);
            $lastOrder = $this->homeTabManager->getOrderOfLastWorkspaceHomeTabConfigByWorkspace($workspace);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabWorkspaceCreateEvent($homeTabConfig);
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
                'ClarolineCoreBundle:API:HomeTab\workspaceHomeTabCreateForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTabConfig}/edit/form",
     *     name="api_get_workspace_home_tab_edition_form",
     *     options = {"expose"=true}
     * )
     *
     * Returns the workspace home tab edition form
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getWorkspaceHomeTabEditionFormAction(HomeTabConfig $homeTabConfig)
    {
        $workspace = $homeTabConfig->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceHomeTab($workspace, $homeTab);
        $visible = $homeTabConfig->isVisible();
        $details = $homeTabConfig->getDetails();
        $color = isset($details['color']) ? $details['color'] : null;
        $formType = new HomeTabType('workspace', $color, false, $visible, $workspace);
        $formType->enableApi();
        $form = $this->createForm($formType, $homeTab);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\workspaceHomeTabEditForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTabConfig}/edit",
     *     name="api_put_workspace_home_tab_edition",
     *     options = {"expose"=true}
     * )
     *
     * Edits the workspace home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putWorkspaceHomeTabEditionAction(HomeTabConfig $homeTabConfig)
    {
        $workspace = $homeTabConfig->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceHomeTab($workspace, $homeTab);
        $formType = new HomeTabType('workspace', null, false, true, $workspace);
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $color = $form->get('color')->getData();
            $visible = $form->get('visible')->getData();
            $roles = $formDatas['roles'];
            $homeTab->emptyRoles();

            foreach ($roles as $role) {
                $homeTab->addRole($role);
            }
            $homeTab->setName($formDatas['name']);
            $homeTabConfig->setVisible($visible);
            $details = $homeTabConfig->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['color'] = $color;
            $homeTabConfig->setDetails($details);
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabWorkspaceEditEvent($homeTabConfig);
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
                'ClarolineCoreBundle:API:HomeTab\workspaceHomeTabEditForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTabConfig}/delete",
     *     name="api_delete_workspace_home_tab",
     *     options = {"expose"=true}
     * )
     *
     * Deletes workspace home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteWorkspaceHomeTabAction(HomeTabConfig $homeTabConfig)
    {
        $workspace = $homeTabConfig->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceHomeTab($workspace, $homeTab);
        $htcDatas = $this->serializer->serialize($homeTabConfig, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        $this->homeTabManager->deleteHomeTabConfig($homeTabConfig);
        $this->homeTabManager->deleteHomeTab($homeTab);
        $event = new LogHomeTabWorkspaceDeleteEvent($workspace, json_decode($htcDatas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($htcDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTabConfig}/next/{nextHomeTabConfigId}/reorder",
     *     name="api_post_workspace_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Update workspace HomeTabConfig order
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAdminHomeTabConfigReorderAction(HomeTabConfig $homeTabConfig, $nextHomeTabConfigId)
    {
        $workspace = $homeTabConfig->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkWorkspaceHomeTab($workspace, $homeTab);

        $this->homeTabManager->reorderWorkspaceHomeTabConfigs(
            $workspace,
            $homeTabConfig,
            $nextHomeTabConfigId
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTabConfig}/bookmark",
     *     name="api_post_workspace_home_tab_bookmark",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Bookmark the given workspace homeTab.
     *
     * @return Response
     */
    public function postWorkspaceHomeTabBookmarkAction(User $user, HomeTabConfig $homeTabConfig)
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkRoleAccessToHomeTab($homeTab);
        $userHTC = $this->homeTabManager->getOneVisibleWorkspaceUserHTC($homeTab, $user);

        if (is_null($userHTC)) {
            $workspace = $homeTab->getWorkspace();
            $userHTC = new HomeTabConfig();
            $userHTC->setHomeTab($homeTab);
            $userHTC->setUser($user);
            $userHTC->setWorkspace($workspace);
            $userHTC->setType('workspace_user');
            $lastOrder = $this->homeTabManager->getOrderOfLastWorkspaceUserHomeTabByUser($user);

            if (is_null($lastOrder['order_max'])) {
                $userHTC->setTabOrder(1);
            } else {
                $userHTC->setTabOrder($lastOrder['order_max'] + 1);
            }
            $userHTC->setDetails($homeTabConfig->getDetails());
            $this->homeTabManager->insertHomeTabConfig($userHTC);
            $event = new LogHomeTabWorkspacePinEvent($homeTabConfig);
            $this->eventDispatcher->dispatch('log', $event);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTab}/widgets/display",
     *     name="api_get_workspace_widgets_display",
     *     options = {"expose"=true}
     * )
     *
     * Retrieves workspace widgets
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getWorkspaceWidgetsAction(HomeTab $homeTab)
    {
        $this->checkRoleAccessToHomeTab($homeTab);
        $widgets = [];
        $workspace = $homeTab->getWorkspace();
        $canEdit = $this->hasWorkspaceEditionAccess($workspace);
        $configs = $canEdit ?
            $this->homeTabManager->getWidgetConfigsByWorkspace($homeTab, $workspace) :
            $this->homeTabManager->getVisibleWidgetConfigsByTabIdAndWorkspace($homeTab->getId(), $workspace);
        $wdcs = $this->widgetManager->generateWidgetDisplayConfigsForWorkspace($workspace, $configs);

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
     *     "/api/workspace/home/tab/{homeTab}/widget/create/form",
     *     name="api_get_workspace_widget_instance_creation_form",
     *     options = {"expose"=true}
     * )
     *
     * Returns the widget instance creation form
     */
    public function getWorkspaceInstanceCreationFormAction(HomeTab $homeTab)
    {
        $this->checkToolEditionAccess($homeTab->getWorkspace());
        $formType = new WidgetInstanceConfigType('workspace', $this->bundles);
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Widget\widgetInstanceCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/home/tab/{homeTab}/widget/create",
     *     name="api_post_workspace_widget_instance_creation",
     *     options = {"expose"=true}
     * )
     *
     * Creates a new widget instance
     */
    public function postWorkspaceWidgetInstanceCreationAction(HomeTab $homeTab)
    {
        $workspace = $homeTab->getWorkspace();
        $this->checkToolEditionAccess($homeTab->getWorkspace());
        $formType = new WidgetInstanceConfigType('workspace', $this->bundles);
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $widget = $formDatas['widget'];
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();
            $visible = $form->get('visible')->getData();

            $widgetInstance = new WidgetInstance();
            $widgetHomeTabConfig = new WidgetHomeTabConfig();
            $widgetDisplayConfig = new WidgetDisplayConfig();
            $widgetInstance->setName($formDatas['name']);
            $widgetInstance->setWidget($widget);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(false);
            $widgetInstance->setWorkspace($workspace);
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setVisible($visible);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('workspace');
            $widgetHomeTabConfig->setWorkspace($workspace);
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setWorkspace($workspace);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());
            $widgetDisplayConfig->setColor($color);
            $widgetDisplayConfig->setDetails(['textTitleColor' => $textTitleColor]);

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );
            $event = new LogWidgetWorkspaceCreateEvent($homeTab, $widgetHomeTabConfig, $widgetDisplayConfig);
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
     *     "/api/workspace/home/tab/widget/config/{whtc}/display/{wdc}/edit/form",
     *     name="api_get_workspace_widget_instance_edition_form",
     *     options = {"expose"=true}
     * )
     *
     * Returns the widget instance edition form
     */
    public function getWorkspaceWidgetInstanceEditionFormAction(WidgetHomeTabConfig $whtc, WidgetDisplayConfig $wdc)
    {
        $workspace = $whtc->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $widgetInstance = $wdc->getWidgetInstance();
        $this->checkWorkspaceWidgetInstance($workspace, $widgetInstance);
        $this->checkWorkspaceWidgetDisplayConfig($workspace, $wdc);
        $widget = $widgetInstance->getWidget();
        $visible = $whtc->isVisible();
        $locked = $whtc->isLocked();
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('workspace', $this->bundles, false, [], $color, $textTitleColor, $locked, $visible, false);
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
     *     "/api/workspace/home/tab/widget/config/{whtc}/display/{wdc}/edit",
     *     name="api_put_workspace_widget_instance_edition",
     *     options = {"expose"=true}
     * )
     *
     * Edits widget instance config
     */
    public function putWorkspaceWidgetInstanceEditionAction(WidgetHomeTabConfig $whtc, WidgetDisplayConfig $wdc)
    {
        $workspace = $whtc->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $widgetInstance = $wdc->getWidgetInstance();
        $this->checkWorkspaceWidgetInstance($workspace, $widgetInstance);
        $this->checkWorkspaceWidgetDisplayConfig($workspace, $wdc);
        $widget = $widgetInstance->getWidget();
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $visible = $whtc->isVisible();
        $locked = $whtc->isLocked();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('workspace', $this->bundles, false, [], $color, $textTitleColor, $locked, $visible, false);
        $formType->enableApi();
        $form = $this->createForm($formType, $widgetInstance);
        $form->submit($this->request);

        if ($form->isValid()) {
            $instance = $form->getData();
            $name = $instance->getName();
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();
            $visible = $form->get('visible')->getData();
            $widgetInstance->setName($name);
            $whtc->setVisible($visible);
            $wdc->setColor($color);
            $details = $wdc->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['textTitleColor'] = $textTitleColor;
            $wdc->setDetails($details);

            $this->widgetManager->persistWidgetConfigs($widgetInstance, null, $wdc);
            $event = new LogWidgetWorkspaceEditEvent($widgetInstance, $whtc, $wdc);
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
     *     "/api/workspace/home/tab/widget/{widgetHomeTabConfig}/delete",
     *     name="api_delete_workspace_widget_home_tab_config",
     *     options = {"expose"=true}
     * )
     *
     * Deletes a widget
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAdminWidgetHomeTabConfigAction(WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $workspace = $widgetHomeTabConfig->getWorkspace();
        $this->checkToolEditionAccess($workspace);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
        $datas = $this->serializer->serialize($widgetInstance, 'json', SerializationContext::create()->setGroups(['api_widget']));
        $this->homeTabManager->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
        $this->widgetManager->removeInstance($widgetInstance);
        $event = new LogWidgetWorkspaceDeleteEvent($workspace, json_decode($datas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/workspace/widget/display/{datas}/update",
     *     name="api_put_workspace_widget_display_update",
     *     options = {"expose"=true}
     * )
     *
     * Updates widgets display
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putWorkspaceWidgetDisplayUpdateAction($datas)
    {
        $jsonDatas = json_decode($datas, true);
        $displayConfigs = [];

        foreach ($jsonDatas as $data) {
            $displayConfig = $this->widgetManager->getWidgetDisplayConfigById($data['id']);

            if (!is_null($displayConfig)) {
                $workspace = $displayConfig->getWorkspace();
                $this->checkToolEditionAccess($workspace);
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

    private function checkToolAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('home', $workspace)) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function checkToolEditionAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted(['home', 'edit'], $workspace)) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function checkWorkspaceHomeTab(Workspace $workspace, HomeTab $homeTab)
    {
        $htWorkspace = $homeTab->getWorkspace();

        if ($workspace !== $htWorkspace) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function checkRoleAccessToHomeTab(HomeTab $homeTab)
    {
        $workspace = $homeTab->getWorkspace();
        $this->checkToolAccess($workspace);
        $canEdit = $this->hasWorkspaceEditionAccess($workspace);
        $homeTabRoles = $homeTab->getRoles();
        $hasAccess = $canEdit || 0 === count($homeTabRoles);

        if (!$hasAccess) {
            $userRoleNames = $this->utils->getRoles($this->tokenStorage->getToken());

            foreach ($homeTabRoles as $role) {
                $homeTabRoleName = $role->getName();

                if (in_array($homeTabRoleName, $userRoleNames)) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function checkWorkspaceWidgetInstance(Workspace $workspace, WidgetInstance $widgetInstance)
    {
        $htWorkspace = $widgetInstance->getWorkspace();

        if ($workspace !== $htWorkspace) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function checkWorkspaceWidgetDisplayConfig(Workspace $workspace, WidgetDisplayConfig $wdc)
    {
        $htWorkspace = $wdc->getWorkspace();

        if ($workspace !== $htWorkspace) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }

    private function hasWorkspaceEditionAccess(Workspace $workspace)
    {
        return $this->authorization->isGranted(['home', 'edit'], $workspace);
    }
}
