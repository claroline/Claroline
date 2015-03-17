<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class DesktopController extends Controller
{
    private $em;
    private $eventDispatcher;
    private $homeTabManager;
    private $request;
    private $router;
    private $toolManager;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "router"             = @DI\Inject("router"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "widgetManager"      = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        HomeTabManager $homeTabManager,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        ToolManager $toolManager,
        WidgetManager $widgetManager
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/no_config/widgets",
     *     name="claro_desktop_widgets_without_config"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Widget:widgetsWithoutConfig.html.twig")
     *
     * Displays registered widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsWithoutConfigAction($homeTabId, User $user)
    {
        $widgets = array();
        $configs = array();

        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);

        if (is_null($homeTab)) {
            $isVisibleHomeTab = false;
        } else {
            $isVisibleHomeTab = $this->homeTabManager
                ->checkHomeTabVisibilityByUser($homeTab, $user);
            $isLockedHomeTab = $this->homeTabManager->checkHomeTabLock($homeTab);
        }

        if ($isVisibleHomeTab) {

            if ($homeTab->getType() === 'admin_desktop') {
                $adminConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);

                if (!$isLockedHomeTab) {
                    $userWidgetsConfigs = $this->homeTabManager
                        ->getVisibleWidgetConfigsByUser($homeTab, $user);
                } else {
                    $userWidgetsConfigs = array();
                }

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
                        if (count($existingWidgetConfig) === 0) {
                            $newWHTC = new WidgetHomeTabConfig();
                            $newWHTC->setHomeTab($homeTab);
                            $newWHTC->setWidgetInstance($adminConfig->getWidgetInstance());
                            $newWHTC->setUser($user);
                            $newWHTC->setWidgetOrder($adminConfig->getWidgetOrder());
                            $newWHTC->setVisible($adminConfig->isVisible());
                            $newWHTC->setLocked(false);
                            $newWHTC->setType('admin_desktop');
                            $this->homeTabManager->insertWidgetHomeTabConfig($newWHTC);

                            if ($adminConfig->isVisible()) {
                                $configs[] = $newWHTC;
                            }
                        } elseif ($existingWidgetConfig[0]->isVisible()) {
                            $configs[] = $existingWidgetConfig[0];
                        }
                    }
                }

                foreach ($userWidgetsConfigs as $userWidgetsConfig) {
                    $configs[] = $userWidgetsConfig;
                }
            } else {
                $configs = $this->homeTabManager->getVisibleWidgetConfigsByUser($homeTab, $user);
            }

            foreach ($configs as $config) {
                $event = $this->eventDispatcher->dispatch(
                    "widget_{$config->getWidgetInstance()->getWidget()->getName()}",
                    'DisplayWidget',
                    array($config->getWidgetInstance())
                );

                $widget['config'] = $config;
                $widget['content'] = $event->getContent();
                $widgets[] = $widget;
            }
        }

        return array('widgetsDatas' => $widgets);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/display/desktop/widgets",
     *     name="claro_desktop_display_widgets"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Widget:desktopWidgets.html.twig")
     *
     * Displays visible widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayDesktopWidgetsAction($homeTabId, User $user)
    {
        $widgets = array();
        $configs = array();
        $lastWidgetOrder = 1;
        $isLockedHomeTab = false;
        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);

        if (is_null($homeTab)) {
            $isVisibleHomeTab = false;
        } else {
            $isVisibleHomeTab = $this->homeTabManager
                ->checkHomeTabVisibilityForConfigByUser($homeTab, $user);
            $isLockedHomeTab = $this->homeTabManager->checkHomeTabLock($homeTab);
        }

        if ($isVisibleHomeTab) {

            if ($homeTab->getType() === 'admin_desktop') {
                $adminConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);

                if (!$isLockedHomeTab) {
                    $userWidgetsConfigs = $this->homeTabManager
                        ->getWidgetConfigsByUser($homeTab, $user);
                } else {
                    $userWidgetsConfigs = array();
                }

                if (count($userWidgetsConfigs) > 0) {
                    $lastWidgetOrder = count($userWidgetsConfigs);
                }

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
                        if (count($existingWidgetConfig) === 0) {
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
                        } else {
                            $configs[] = $existingWidgetConfig[0];
                        }
                    }
                }

                foreach ($userWidgetsConfigs as $userWidgetsConfig) {
                    $configs[] = $userWidgetsConfig;
                }
            } else {
                $configs = $this->homeTabManager->getWidgetConfigsByUser($homeTab, $user);

                if (count($configs) > 0) {
                    $lastWidgetOrder = count($configs);
                }
            }
            $userWDCs = $this->widgetManager->generateWidgetDisplayConfigsForUser($user, $configs);

            foreach ($configs as $config) {
                $event = $this->eventDispatcher->dispatch(
                    "widget_{$config->getWidgetInstance()->getWidget()->getName()}",
                    'DisplayWidget',
                    array($config->getWidgetInstance())
                );

                $widget['config'] = $config;
                $widget['content'] = $event->getContent();
                $widget['configurable'] = $config->isLocked() !== true
                    && $config->getWidgetInstance()->getWidget()->isConfigurable();
                $widgetInstanceId = $config->getWidgetInstance()->getId();
                $widget['widgetDisplayConfig'] = $userWDCs[$widgetInstanceId];
                $widgets[] = $widget;
            }
        }

        return array(
            'widgetsDatas' => $widgets,
            'isVisibleHomeTab' => $isVisibleHomeTab,
            'isLockedHomeTab' => $isLockedHomeTab,
            'lastWidgetOrder' => $lastWidgetOrder,
            'homeTabId' => $homeTabId
        );
    }

    /**
     * @EXT\Template()
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Renders the left tool bar. Not routed.
     *
     * @return Response
     */
    public function renderToolListAction(User $user)
    {
        return array('tools' => $this->toolManager->getDisplayedDesktopOrderedTools($user));
    }

    /**
     * @EXT\Route(
     *     "tool/open/{toolName}",
     *     name="claro_desktop_open_tool",
     *     options={"expose"=true}
     * )
     *
     * Opens a tool.
     *
     * @param string $toolName
     *
     * @throws \Exception
     * @return Response
     */
    public function openToolAction($toolName)
    {
        $event = $this->eventDispatcher->dispatch(
            'open_tool_desktop_'.$toolName,
            'DisplayTool'
        );

        if ($toolName === 'resource_manager') {
            $this->get('session')->set('isDesktop', true);
        }

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/open",
     *     name="claro_desktop_open"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Opens the desktop.
     *
     * @param User $user
     *
     * @return Response
     */
    public function openAction(User $user)
    {
        $route = $this->router->generate(
            'claro_desktop_open_tool',
            array('toolName' => 'home')
        );

        return new RedirectResponse($route);
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
    public function updateWidgetsDisplayConfigAction(
        User $user,
        array $widgetDisplayConfigs
    )
    {
        $toPersist = array();

        foreach ($widgetDisplayConfigs as $config) {

            if ($config->getUser() !== $user) {

                throw new AccessDeniedException();
            }
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
}
