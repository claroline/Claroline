<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    private $em;
    private $eventDispatcher;
    private $homeTabManager;
    private $router;
    private $toolManager;


    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        HomeTabManager $homeTabManager,
        UrlGeneratorInterface $router,
        ToolManager $toolManager
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->router = $router;
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/widgets",
     *     name="claro_desktop_widgets"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Widget:widgets.html.twig")
     *
     * Displays registered widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction($homeTabId, User $user)
    {
        $widgets = array();
        $configs = array();

        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);

        if (!is_null($homeTab) &&
            $this->homeTabManager->checkHomeTabVisibilityByUser($homeTab, $user)) {

            if ($homeTab->getType() === 'admin_desktop') {
                $adminConfigs = $this->homeTabManager->getAdminWidgetConfigs($homeTab);
                $userWidgetsConfigs = $this->homeTabManager
                    ->getWidgetConfigsByUser($homeTab, $user);

                foreach ($adminConfigs as $adminConfig) {

                    if ($adminConfig->isLocked()) {
                        $configs[] = $adminConfig;
                    }
                    else {
                        $existingWidgetConfig = $this->homeTabManager
                            ->getUserAdminWidgetHomeTabConfig(
                                $homeTab,
                                $adminConfig->getWidget(),
                                $user
                            );
                        if (count($existingWidgetConfig) === 0) {
                            $newWHTC = new WidgetHomeTabConfig();
                            $newWHTC->setHomeTab($homeTab);
                            $newWHTC->setWidget($adminConfig->getWidget());
                            $newWHTC->setUser($user);
                            $newWHTC->setWidgetOrder($adminConfig->getWidgetOrder());
                            $newWHTC->setVisible($adminConfig->isVisible());
                            $newWHTC->setLocked(false);
                            $newWHTC->setType('admin_desktop');
                            $this->homeTabManager->insertWidgetHomeTabConfig($newWHTC);
                            $configs[] = $newWHTC;
                        }
                        else {
                            $configs[] = $existingWidgetConfig[0];
                        }
                    }
                }

                foreach ($userWidgetsConfigs as $userWidgetsConfig) {
                    $configs[] = $userWidgetsConfig;
                }
            }
            else {
                $configs = $this->homeTabManager->getWidgetConfigsByUser($homeTab, $user);
            }

            foreach ($configs as $config) {
                if ($config->isVisible()) {
                    $event = $this->eventDispatcher->dispatch(
                        "widget_{$config->getWidget()->getName()}_desktop",
                        'DisplayWidget'
                    );

                    if ($event->hasContent()) {
                        $widget['id'] = $config->getWidget()->getId();
                        if ($event->hasTitle()) {
                            $widget['title'] = $event->getTitle();
                        } else {
                            $widget['title'] = strtolower($config->getWidget()->getName());
                        }
                        $widget['content'] = $event->getContent();
                        $widget['configurable'] = ($config->isLocked() !== true and $config->getWidget()->isConfigurable());

                        $widgets[] = $widget;
                    }
                }
            }
        }

        return array(
            'widgets' => $widgets,
            'isDesktop' => true
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
     * @return Response
     */
    public function openAction(User $user)
    {
        $openedTool = $this->toolManager->getDisplayedDesktopOrderedTools($user);

        $route = $this->router->generate(
            'claro_desktop_open_tool',
            array('toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }
}
