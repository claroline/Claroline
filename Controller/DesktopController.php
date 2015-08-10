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
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    private $roleManager;
    private $router;
    private $toolManager;
    private $userManager;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "widgetManager"      = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        HomeTabManager $homeTabManager,
        RequestStack $requestStack,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        ToolManager $toolManager,
        UserManager $userManager,
        WidgetManager $widgetManager
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->userManager = $userManager;
        $this->widgetManager = $widgetManager;
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
        $isLockedHomeTab = false;
        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);
        $initWidgetsPosition = false;
        $isWorkspace = false;

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
            } elseif ($homeTab->getType() === 'desktop') {
                $configs = $this->homeTabManager->getWidgetConfigsByUser($homeTab, $user);
            } elseif ($homeTab->getType() === 'workspace') {
                $workspace = $homeTab->getWorkspace();
                $isLockedHomeTab = true;
                $isWorkspace = true;
                $configs = $this->homeTabManager->getWidgetConfigsByWorkspace(
                    $homeTab,
                    $workspace
                );
            }

            $wdcs = $isWorkspace ?
                $this->widgetManager->generateWidgetDisplayConfigsForWorkspace(
                    $workspace,
                    $configs
                ) :
                $this->widgetManager->generateWidgetDisplayConfigsForUser(
                    $user,
                    $configs
                );

            foreach ($wdcs as $wdc) {

                if ($wdc->getRow() === -1 || $wdc->getColumn() === -1) {
                    $initWidgetsPosition = true;
                    break;
                }
            }

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
                $widget['widgetDisplayConfig'] = $wdcs[$widgetInstanceId];
                $widgets[] = $widget;
            }
        }
        $options = $this->userManager->getUserOptions($user);
        $editionMode = $options->getDesktopMode() === 1;
        $isHomeLocked = $this->roleManager->isHomeLocked($user);

        return array(
            'widgetsDatas' => $widgets,
            'isVisibleHomeTab' => $isVisibleHomeTab,
            'isLockedHomeTab' => $isLockedHomeTab,
            'homeTabId' => $homeTabId,
            'initWidgetsPosition' => $initWidgetsPosition,
            'isWorkspace' => $isWorkspace,
            'editionMode' => $editionMode,
            'isHomeLocked' => $isHomeLocked
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
}
