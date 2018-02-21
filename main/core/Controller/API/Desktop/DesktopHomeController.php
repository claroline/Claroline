<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Desktop;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabAdminUserEditEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabUserCreateEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabUserEditEvent;
use Claroline\CoreBundle\Event\Log\LogHomeTabWorkspaceUnpinEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetAdminHideEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetUserCreateEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWidgetUserEditEvent;
use Claroline\CoreBundle\Form\HomeTabType;
use Claroline\CoreBundle\Form\WidgetInstanceConfigType;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DesktopHomeController extends Controller
{
    private $apiManager;
    private $authorization;
    private $bundles;
    private $eventDispatcher;
    private $homeTabManager;
    private $pluginManager;
    private $request;
    private $roleManager;
    private $serializer;
    private $tokenStorage;
    private $userManager;
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
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "serializer"      = @DI\Inject("jms_serializer"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
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
        RoleManager $roleManager,
        Serializer $serializer,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
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
        $this->roleManager = $roleManager;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->utils = $utils;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/options",
     *     name="api_get_desktop_options",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns desktop options
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDesktopOptionsAction(User $user)
    {
        $options = $this->userManager->getUserOptions($user);
        $desktopOptions = [];
        $desktopOptions['editionMode'] = 1 === $options->getDesktopMode();
        $desktopOptions['isHomeLocked'] = $this->roleManager->isHomeLocked($user);

        return new JsonResponse($desktopOptions, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tabs",
     *     name="api_get_desktop_home_tabs",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns list of desktop home tabs
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDesktopHomeTabsAction(User $user)
    {
        $options = $this->userManager->getUserOptions($user);
        $desktopHomeDatas = [
            'tabsAdmin' => [],
            'tabsUser' => [],
            'tabsWorkspace' => [],
        ];
        $desktopHomeDatas['editionMode'] = 1 === $options->getDesktopMode();
        $desktopHomeDatas['isHomeLocked'] = $this->roleManager->isHomeLocked($user);
        $userHomeTabConfigs = [];
        $roleNames = $this->utils->getRoles($this->tokenStorage->getToken());

        if ($desktopHomeDatas['isHomeLocked']) {
            $visibleAdminHomeTabConfigs = $this->homeTabManager
                ->getVisibleAdminDesktopHomeTabConfigsByRoles($roleNames);
            $workspaceUserHTCs = $this->homeTabManager
                ->getVisibleWorkspaceUserHTCsByUser($user);
        } else {
            $adminHomeTabConfigs = $this->homeTabManager
                ->generateAdminHomeTabConfigsByUser($user, $roleNames);
            $visibleAdminHomeTabConfigs = $this->homeTabManager
                ->filterVisibleHomeTabConfigs($adminHomeTabConfigs);
            $userHomeTabConfigs = $this->homeTabManager
                ->getVisibleDesktopHomeTabConfigsByUser($user);
            $workspaceUserHTCs = $this->homeTabManager
                ->getVisibleWorkspaceUserHTCsByUser($user);
        }

        foreach ($visibleAdminHomeTabConfigs as $htc) {
            $desktopHomeDatas['tabsAdmin'][] = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        }

        foreach ($userHomeTabConfigs as $htc) {
            $desktopHomeDatas['tabsUser'][] = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        }

        foreach ($workspaceUserHTCs as $htc) {
            $desktopHomeDatas['tabsWorkspace'][] = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        }

        return new JsonResponse($desktopHomeDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/edition/mode/toggle",
     *     name="api_put_desktop_home_edition_mode_toggle",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Switch desktop home edition mode
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putDesktopHomeEditionModeToggleAction(User $user)
    {
        $options = $this->userManager->switchDesktopMode($user);

        return new JsonResponse($options->getDesktopMode(), 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/admin/home/tab/{htc}/visibility/toggle",
     *     name="api_put_admin_home_tab_visibility_toggle",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Toggle visibility for admin home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putAdminHomeTabVisibilityToggleAction(User $user, HomeTabConfig $htc)
    {
        $this->checkHomeTabConfig($user, $htc, 'admin_desktop');
        $htc->setVisible(!$htc->isVisible());
        $this->homeTabManager->insertHomeTabConfig($htc);
        $event = new LogHomeTabAdminUserEditEvent($htc);
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse(
            $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab'])),
            200
        );
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/create/form",
     *     name="api_get_user_home_tab_creation_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the home tab creation form
     */
    public function getUserHomeTabCreationFormAction()
    {
        $this->checkHomeLocked();
        $formType = new HomeTabType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\userHomeTabCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/create",
     *     name="api_post_user_home_tab_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a desktop home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postUserHomeTabCreationAction(User $user)
    {
        $this->checkHomeLocked();
        $formType = new HomeTabType();
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $formDatas = $form->getData();
            $color = $form->get('color')->getData();

            $homeTab = new HomeTab();
            $homeTab->setName($formDatas['name']);
            $homeTab->setType('desktop');
            $homeTab->setUser($user);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('desktop');
            $homeTabConfig->setUser($user);
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);
            $homeTabConfig->setDetails(['color' => $color]);

            $lastOrder = $this->homeTabManager->getOrderOfLastDesktopHomeTabConfigByUser($user);

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            } else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabUserCreateEvent($homeTabConfig);
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
                'ClarolineCoreBundle:API:HomeTab\userHomeTabCreateForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/{homeTab}/edit/form",
     *     name="api_get_user_home_tab_edition_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the home tab edition form
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUserHomeTabEditionFormAction(User $user, HomeTab $homeTab)
    {
        $this->checkHomeLocked();
        $this->checkHomeTabEdition($homeTab, $user);

        $homeTabConfig = $this->homeTabManager->getHomeTabConfigByHomeTabAndUser($homeTab, $user);
        $details = !is_null($homeTabConfig) ? $homeTabConfig->getDetails() : null;
        $color = isset($details['color']) ? $details['color'] : null;

        $formType = new HomeTabType('desktop', $color);
        $formType->enableApi();
        $form = $this->createForm($formType, $homeTab);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:HomeTab\userHomeTabEditForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/{homeTab}/edit",
     *     name="api_put_user_home_tab_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Edits a home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putUserHomeTabEditionAction(User $user, HomeTab $homeTab)
    {
        $this->checkHomeLocked();
        $this->checkHomeTabEdition($homeTab, $user);

        $formType = new HomeTabType();
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $homeTabConfig = $this->homeTabManager->getHomeTabConfigByHomeTabAndUser($homeTab, $user);

            if (is_null($homeTabConfig)) {
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
            }
            $formDatas = $form->getData();
            $homeTab->setName($formDatas['name']);
            $color = $form->get('color')->getData();
            $details = $homeTabConfig->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['color'] = $color;
            $homeTabConfig->setDetails($details);
            $this->homeTabManager->persistHomeTabConfigs($homeTab, $homeTabConfig);
            $event = new LogHomeTabUserEditEvent($homeTabConfig);
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
                'ClarolineCoreBundle:API:HomeTab\userHomeTabEditForm.html.twig',
                $form,
                $options
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/{htc}/delete",
     *     name="api_delete_user_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Deletes user home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteUserHomeTabAction(User $user, HomeTabConfig $htc)
    {
        $this->checkHomeTabConfig($user, $htc, 'desktop');
        $tab = $htc->getHomeTab();
        $htcDatas = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        $this->homeTabManager->deleteHomeTabConfig($htc);
        $this->homeTabManager->deleteHomeTab($tab);
        $event = new LogHomeTabUserDeleteEvent(json_decode($htcDatas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($htcDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/pinned/home/tab/{htc}/delete",
     *     name="api_delete_pinned_workspace_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Delete pinned workspace home tab
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deletePinnedWorkspaceHomeTabAction(User $user, HomeTabConfig $htc)
    {
        $this->checkHomeTabConfig($user, $htc, 'workspace_user');
        $workspace = $htc->getWorkspace();
        $htcDatas = $this->serializer->serialize($htc, 'json', SerializationContext::create()->setGroups(['api_home_tab']));
        $this->homeTabManager->deleteHomeTabConfig($htc);
        $event = new LogHomeTabWorkspaceUnpinEvent($user, $workspace, json_decode($htcDatas, true));
        $this->eventDispatcher->dispatch('log', $event);

        return new JsonResponse($htcDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/{homeTabConfig}/next/{nextHomeTabConfigId}/reorder",
     *     name="api_post_desktop_home_tab_config_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Update desktop HomeTabConfig order
     *
     * @return Response
     */
    public function postDesktopHomeTabConfigReorderAction(
        User $user,
        HomeTabConfig $homeTabConfig,
        $nextHomeTabConfigId
    ) {
        $homeTab = $homeTabConfig->getHomeTab();
        $this->checkHomeTabEdition($homeTab, $user);

        $this->homeTabManager->reorderDesktopHomeTabConfigs(
            $user,
            $homeTabConfig,
            $nextHomeTabConfigId
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/{homeTab}/widgets/display",
     *     name="api_get_desktop_widgets_display",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Retrieves desktop widgets
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDesktopWidgetsAction(HomeTab $homeTab)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $isVisibleHomeTab = $this->homeTabManager->checkHomeTabVisibilityForConfigByUser($homeTab, $user);
        $isLockedHomeTab = $this->homeTabManager->checkHomeTabLock($homeTab);
        $isHomeLocked = $this->roleManager->isHomeLocked($user);
        $isWorkspace = false;
        $configs = [];
        $widgets = [];
        $widgetsDatas = [
            'isLockedHomeTab' => $isLockedHomeTab,
            'initWidgetsPosition' => false,
            'widgets' => [],
        ];

        if ($isVisibleHomeTab) {
            if ('admin_desktop' === $homeTab->getType()) {
                $adminConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);

                if ($isLockedHomeTab || $isHomeLocked) {
                    foreach ($adminConfigs as $adminConfig) {
                        if ($adminConfig->isVisible()) {
                            $configs[] = $adminConfig;
                        }
                    }
                } else {
                    $userWidgetsConfigs = $this->homeTabManager
                        ->getWidgetConfigsByUser($homeTab, $user);

                    foreach ($adminConfigs as $adminConfig) {
                        if ($adminConfig->isLocked()) {
                            if ($adminConfig->isVisible()) {
                                $configs[] = $adminConfig;
                            }
                        } else {
                            $existingWidgetConfig = $this->homeTabManager
                                ->getUserAdminWidgetHomeTabConfig(
                                    $homeTab,
                                    $adminConfig->getWidgetInstance(),
                                    $user
                                );
                            if (is_null($existingWidgetConfig) && $adminConfig->isVisible()) {
                                $newWHTC = new WidgetHomeTabConfig();
                                $newWHTC->setHomeTab($homeTab);
                                $newWHTC->setWidgetInstance($adminConfig->getWidgetInstance());
                                $newWHTC->setUser($user);
                                $newWHTC->setWidgetOrder($adminConfig->getWidgetOrder());
                                $newWHTC->setVisible($adminConfig->isVisible());
                                $newWHTC->setLocked(false);
                                $newWHTC->setType('admin_desktop');
                                $this->homeTabManager->insertWidgetHomeTabConfig($newWHTC);
                                $configs[] = $newWHTC;
                            } elseif ($existingWidgetConfig->isVisible()) {
                                $configs[] = $existingWidgetConfig;
                            }
                        }
                    }

                    foreach ($userWidgetsConfigs as $userWidgetsConfig) {
                        $configs[] = $userWidgetsConfig;
                    }
                }
            } elseif ('desktop' === $homeTab->getType()) {
                $configs = $this->homeTabManager->getWidgetConfigsByUser($homeTab, $user);
            } elseif ('workspace' === $homeTab->getType()) {
                $workspace = $homeTab->getWorkspace();
                $widgetsDatas['isLockedHomeTab'] = true;
                $isWorkspace = true;
                $configs = $this->homeTabManager->getWidgetConfigsByWorkspace(
                    $homeTab,
                    $workspace
                );
            }

            if ($isWorkspace) {
                $wdcs = $this->widgetManager->generateWidgetDisplayConfigsForWorkspace(
                    $workspace,
                    $configs
                );
            } elseif ($isLockedHomeTab || $isHomeLocked) {
                $wdcs = $this->widgetManager->getAdminWidgetDisplayConfigsByWHTCs($configs);
            } else {
                $wdcs = $this->widgetManager->generateWidgetDisplayConfigsForUser(
                    $user,
                    $configs
                );
            }

            foreach ($wdcs as $wdc) {
                if ($wdc->getRow() === -1 || $wdc->getColumn() === -1) {
                    $widgetsDatas['initWidgetsPosition'] = true;
                    break;
                }
            }

            foreach ($configs as $config) {
                $widgetDatas = [];
                $widgetInstance = $config->getWidgetInstance();
                $widgetInstanceId = $widgetInstance->getId();
                $widgetDatas['config'] = $this->serializer->serialize($config, 'json', SerializationContext::create()->setGroups(['api_widget']));
                $widgetDatas['display'] = $this->serializer->serialize($wdcs[$widgetInstanceId], 'json', SerializationContext::create()->setGroups(['api_widget']));
                $event = $this->eventDispatcher->dispatch(
                    "widget_{$config->getWidgetInstance()->getWidget()->getName()}",
                    new DisplayWidgetEvent($config->getWidgetInstance())
                );
                $widgetDatas['configurable'] = !$config->isLocked() && $widgetInstance->getWidget()->isConfigurable();
                $widgetDatas['content'] = $event->getContent();
                $widgets[] = $widgetDatas;
            }
            $widgetsDatas['widgets'] = $widgets;
        }

        return new JsonResponse($widgetsDatas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/home/tab/{htc}/widget/create/form",
     *     name="api_get_widget_instance_creation_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the widget instance creation form
     */
    public function getWidgetInstanceCreationFormAction(User $user, HomeTabConfig $htc)
    {
        $this->checkWidgetCreation($user, $htc);
        $formType = new WidgetInstanceConfigType('desktop', $this->bundles, true, $user->getEntityRoles());
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Widget\widgetInstanceCreateForm.html.twig',
            $form
        );
    }

    /**
     * @EXT\Route(
     *     "/api/home/tab/widget/{wdc}/edit/form",
     *     name="api_get_widget_instance_edition_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the widget instance edition form
     */
    public function getWidgetInstanceEditionFormAction(User $user, WidgetDisplayConfig $wdc)
    {
        $this->checkWidgetDisplayConfigEdition($user, $wdc);
        $widgetInstance = $wdc->getWidgetInstance();
        $widget = $widgetInstance->getWidget();
        $this->checkWidgetInstanceEdition($user, $widgetInstance);
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('desktop', $this->bundles, false, [], $color, $textTitleColor, false, true, false);
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
     *     "/api/home/tab/widget/{widgetInstance}/content/configure/form/{admin}",
     *     name="api_get_widget_instance_content_configuration_form",
     *     defaults={"admin"=""},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns the widget instance content configuration form
     */
    public function getWidgetInstanceContentConfigurationFormAction(WidgetInstance $widgetInstance, $admin = '')
    {
        $widget = $widgetInstance->getWidget();
        if ($widget->isConfigurable()) {
            $event = $this->eventDispatcher->dispatch(
                "widget_{$widgetInstance->getWidget()->getName()}_configuration",
                new ConfigureWidgetEvent($widgetInstance, !empty($admin))
            );
            $content = $event->getContent();
        } else {
            $content = null;
        }

        return new JsonResponse($content);
    }

    /**
     * @EXT\Route(
     *     "/api/home/tab/{htc}/widget/create",
     *     name="api_post_desktop_widget_instance_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a new widget instance
     */
    public function postDesktopWidgetInstanceCreationAction(User $user, HomeTabConfig $htc)
    {
        $this->checkWidgetCreation($user, $htc);
        $formType = new WidgetInstanceConfigType('desktop', $this->bundles, true, $user->getEntityRoles());
        $formType->enableApi();
        $form = $this->createForm($formType);
        $form->submit($this->request);

        if ($form->isValid()) {
            $homeTab = $htc->getHomeTab();
            $formDatas = $form->getData();
            $widget = $formDatas['widget'];
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();

            $widgetInstance = new WidgetInstance();
            $widgetHomeTabConfig = new WidgetHomeTabConfig();
            $widgetDisplayConfig = new WidgetDisplayConfig();
            $widgetInstance->setName($formDatas['name']);
            $widgetInstance->setUser($user);
            $widgetInstance->setWidget($widget);
            $widgetInstance->setIsAdmin(false);
            $widgetInstance->setIsDesktop(true);
            $widgetHomeTabConfig->setHomeTab($homeTab);
            $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
            $widgetHomeTabConfig->setUser($user);
            $widgetHomeTabConfig->setVisible(true);
            $widgetHomeTabConfig->setLocked(false);
            $widgetHomeTabConfig->setWidgetOrder(1);
            $widgetHomeTabConfig->setType('desktop');
            $widgetDisplayConfig->setWidgetInstance($widgetInstance);
            $widgetDisplayConfig->setUser($user);
            $widgetDisplayConfig->setWidth($widget->getDefaultWidth());
            $widgetDisplayConfig->setHeight($widget->getDefaultHeight());
            $widgetDisplayConfig->setColor($color);
            $widgetDisplayConfig->setDetails(['textTitleColor' => $textTitleColor]);

            $this->widgetManager->persistWidgetConfigs(
                $widgetInstance,
                $widgetHomeTabConfig,
                $widgetDisplayConfig
            );
            $event = new LogWidgetUserCreateEvent($homeTab, $widgetHomeTabConfig, $widgetDisplayConfig);
            $this->eventDispatcher->dispatch('log', $event);

            $widgetDatas = [
                'config' => $this->serializer->serialize($widgetHomeTabConfig, 'json', SerializationContext::create()->setGroups(['api_widget'])),
                'display' => $this->serializer->serialize($widgetDisplayConfig, 'json', SerializationContext::create()->setGroups(['api_widget'])),
                'configurable' => true !== $widgetHomeTabConfig->isLocked() && $widget->isConfigurable(),
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
     *     "/api/home/tab/widget/{wdc}/edit",
     *     name="api_put_widget_instance_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Edits widget instance config
     */
    public function putWidgetInstanceEditionAction(User $user, WidgetDisplayConfig $wdc)
    {
        $this->checkWidgetDisplayConfigEdition($user, $wdc);
        $widgetInstance = $wdc->getWidgetInstance();
        $widget = $widgetInstance->getWidget();
        $this->checkWidgetInstanceEdition($user, $widgetInstance);
        $color = $wdc->getColor();
        $details = $wdc->getDetails();
        $textTitleColor = isset($details['textTitleColor']) ? $details['textTitleColor'] : null;
        $formType = new WidgetInstanceConfigType('desktop', $this->bundles, false, [], $color, $textTitleColor, false, true, false);
        $formType->enableApi();
        $form = $this->createForm($formType, $widgetInstance);
        $form->submit($this->request);

        if ($form->isValid()) {
            $instance = $form->getData();
            $name = $instance->getName();
            $color = $form->get('color')->getData();
            $textTitleColor = $form->get('textTitleColor')->getData();
            $widgetInstance->setName($name);
            $wdc->setColor($color);
            $details = $wdc->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $details['textTitleColor'] = $textTitleColor;
            $wdc->setDetails($details);

            $this->widgetManager->persistWidgetConfigs($widgetInstance, null, $wdc);
            $event = new LogWidgetUserEditEvent($widgetInstance, null, $wdc);
            $this->eventDispatcher->dispatch('log', $event);

            return new JsonResponse(
                ['display' => $this->serializer->serialize($wdc, 'json', SerializationContext::create()->setGroups(['api_widget']))],
                200
            );
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
     *     "/api/desktop/home/tab/widget/{widgetHomeTabConfig}/visibility/change",
     *     name="api_put_desktop_widget_home_tab_config_visibility_change",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Changes visibility of a widget
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putDesktopWidgetHomeTabConfigVisibilityChangeAction(User $user, WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $this->checkWidgetHomeTabConfigEdition($user, $widgetHomeTabConfig);
        $this->homeTabManager->changeVisibilityWidgetHomeTabConfig($widgetHomeTabConfig, false);
        $homeTab = $widgetHomeTabConfig->getHomeTab();
        $event = new LogWidgetAdminHideEvent($homeTab, $widgetHomeTabConfig);
        $this->eventDispatcher->dispatch('log', $event);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

        return new JsonResponse(
            $this->serializer->serialize($widgetInstance, 'json', SerializationContext::create()->setGroups(['api_widget'])),
            200
        );
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/home/tab/widget/{widgetHomeTabConfig}/delete",
     *     name="api_delete_desktop_widget_home_tab_config",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Deletes a widget
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteDesktopWidgetHomeTabConfigAction(User $user, WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $this->checkWidgetHomeTabConfigEdition($user, $widgetHomeTabConfig);
        $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
        $datas = $this->serializer->serialize($widgetInstance, 'json', SerializationContext::create()->setGroups(['api_widget']));
        $this->homeTabManager->deleteWidgetHomeTabConfig($widgetHomeTabConfig);

        if ($this->hasUserAccessToWidgetInstance($user, $widgetInstance)) {
            $this->widgetManager->removeInstance($widgetInstance);
            $event = new LogWidgetUserDeleteEvent(json_decode($datas, true));
            $this->eventDispatcher->dispatch('log', $event);
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/desktop/widget/display/{datas}/update",
     *     name="api_put_desktop_widget_display_update",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Updates widgets display
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putDesktopWidgetDisplayUpdateAction(User $user, $datas)
    {
        $jsonDatas = json_decode($datas, true);
        $displayConfigs = [];

        foreach ($jsonDatas as $data) {
            $displayConfig = $this->widgetManager->getWidgetDisplayConfigById($data['id']);

            if (!is_null($displayConfig)) {
                $this->checkWidgetDisplayConfigEdition($user, $displayConfig);
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

    private function checkHomeLocked()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('.anon' === $user || $this->roleManager->isHomeLocked($user)) {
            throw new AccessDeniedException();
        }
    }

    private function checkHomeTabConfig(User $authenticatedUser, HomeTabConfig $htc, $homeTabType)
    {
        $user = $htc->getUser();
        $type = $htc->getType();

        if ($type !== $homeTabType || $authenticatedUser !== $user) {
            throw new AccessDeniedException();
        }
    }

    private function checkHomeTabEdition(HomeTab $homeTab, User $user)
    {
        $homeTabUser = $homeTab->getUser();
        $homeTabType = $homeTab->getType();

        if ('desktop' !== $homeTabType || $user !== $homeTabUser) {
            throw new AccessDeniedException();
        }
    }

    private function checkWidgetHomeTabConfigEdition(User $authenticatedUser, WidgetHomeTabConfig $whtc)
    {
        $user = $whtc->getUser();

        if ($authenticatedUser !== $user) {
            throw new AccessDeniedException();
        }
    }

    private function checkWidgetDisplayConfigEdition(User $authenticatedUser, WidgetDisplayConfig $wdc)
    {
        $user = $wdc->getUser();

        if ($authenticatedUser !== $user) {
            throw new AccessDeniedException();
        }
    }

    private function checkWidgetInstanceEdition(User $authenticatedUser, WidgetInstance $widgetInstance)
    {
        $user = $widgetInstance->getUser();

        if ($authenticatedUser !== $user) {
            throw new AccessDeniedException();
        }
    }

    private function checkWidgetCreation(User $user, HomeTabConfig $htc)
    {
        $homeTab = $htc->getHomeTab();
        $homeTabUser = $homeTab->getUser();
        $type = $homeTab->getType();
        $locked = $htc->isLocked();
        $visible = $htc->isVisible();
        $canCreate = $visible &&
            !$locked &&
            (('desktop' === $type && $homeTabUser === $user) || ('admin_desktop' === $type && $visible && !$locked));

        if ('.anon' === $user || $this->roleManager->isHomeLocked($user) || !$canCreate) {
            throw new AccessDeniedException();
        }
    }

    private function hasUserAccessToWidgetInstance(User $authenticatedUser, WidgetInstance $widgetInstance)
    {
        $user = $widgetInstance->getUser();

        return $authenticatedUser === $user;
    }
}
