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

use Claroline\CoreBundle\Form\AdminAnalyticsConnectionsType;
use Claroline\CoreBundle\Form\AdminAnalyticsTopType;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_analytics')")
 */
class AnalyticsController extends Controller
{
    private $userManager;
    private $workspaceManager;
    private $widgetManager;
    private $formFactory;
    private $analyticsManager;
    private $request;
    private $analyticsTool;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "widgetManager"       = @DI\Inject("claroline.manager.widget_manager"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "analyticsManager"    = @DI\Inject("claroline.manager.analytics_manager"),
     *     "request"             = @DI\Inject("request")
     * })
     */
    public function __construct(
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        WidgetManager $widgetManager,
        FormFactory $formFactory,
        AnalyticsManager $analyticsManager,
        Request $request
    ) {
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->widgetManager = $widgetManager;
        $this->formFactory = $formFactory;
        $this->analyticsManager = $analyticsManager;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_analytics_show"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics.html.twig")
     *
     * Displays platform analytics home page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsAction()
    {
        $lastMonthActions = $this->analyticsManager->getDailyActionNumberForDateRange();
        $mostViewedWS = $this->analyticsManager->topWSByAction(null, 'ws_tool_read', 5);
        $mostViewedMedia = $this->analyticsManager->topMediaByAction(null, 'resource_read', 5);
        $mostDownloadedResources = $this->analyticsManager->topResourcesByAction(null, 'resource_export', 5);
        $usersCount = $this->userManager->countUsersForPlatformRoles();

        return [
            'barChartData' => $lastMonthActions,
            'usersCount' => $usersCount,
            'mostViewedWS' => $mostViewedWS,
            'mostViewedMedia' => $mostViewedMedia,
            'mostDownloadedResources' => $mostDownloadedResources,
        ];
    }

    /**
     * @EXT\Route(
     *     "/connections",
     *     name="claro_admin_analytics_connections"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics_connections.html.twig")
     *
     * Displays platform analytics connections page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsConnectionsAction()
    {
        $analyticsType = new AdminAnalyticsConnectionsType();
        $criteriaForm = $this->formFactory->create($analyticsType, [
            'range' => $this->analyticsManager->getDefaultRange(),
            'unique' => 'false',
        ]);

        $criteriaForm->handleRequest($this->request);
        $unique = false;
        $range = null;

        if ($criteriaForm->isValid()) {
            $range = $criteriaForm->get('range')->getData();
            $unique = $criteriaForm->get('unique')->getData() === 'true';
        }

        $actionsForRange = $this->analyticsManager
            ->getDailyActionNumberForDateRange($range, 'user_login', $unique);

        $activeUsersForDateRange = $this->analyticsManager
            ->getActiveUsersForDateRange($range);

        $connections = $actionsForRange;
        $countConnectionsForDateRange = array_sum(array_map(function ($item) {
            return $item[1];
        }, $connections));
        $activeUsers = $this->analyticsManager->getActiveUsers();

        return [
            'connections' => $connections,
            'form_criteria' => $criteriaForm->createView(),
            'activeUsers' => $activeUsers,
            'activeUsersForDateRange' => $activeUsersForDateRange,
            'countConnectionsForDateRange' => $countConnectionsForDateRange,
        ];
    }

    /**
     * @EXT\Route(
     *     "/resources",
     *     name="claro_admin_analytics_resources"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics_resources.html.twig")
     *
     * Displays platform analytics resources page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsResourcesAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $wsCount = $this->workspaceManager->getNbWorkspaces();
        $resourceCount = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->countResourcesByType();

        /** @var \Claroline\CoreBundle\Event\Analytics\PlatformContentItemEvent $event */
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'administration_analytics_platform_content_item_add',
            'Analytics\PlatformContentItem'
        );

        return [
            'wsCount' => $wsCount,
            'resourceCount' => $resourceCount,
            'otherItems' => $event->getItems(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/widgets",
     *     name="claro_admin_analytics_widgets"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics_widgets.html.twig")
     *
     * Displays platform analytics widgets page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsWidgetsAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $wiCount = $this->widgetManager->getNbWidgetInstances();
        $wiwCount = $this->widgetManager->getNbWorkspaceWidgetInstances();
        $widCount = $this->widgetManager->getNbDesktopWidgetInstances();
        $wList = $manager->getRepository('ClarolineCoreBundle:Widget\WidgetInstance')
            ->countByType();

        return [
            'fullWidgetCount' => $wiCount,
            'workspaceWidgetCount' => $wiwCount,
            'desktopWidgetCount' => $widCount,
            'widgetList' => $wList,
        ];
    }

    /**
     * @EXT\Route(
     *     "/top/{topType}",
     *     name="claro_admin_analytics_top",
     *     defaults={"topType" = "top_users_connections"}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics_top.html.twig")
     *
     * Displays platform analytics top activity page
     *
     * @param Request $request
     * @param $topType
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsTopAction(Request $request, $topType)
    {
        $analyticsTopType = new AdminAnalyticsTopType();
        $criteriaForm = $this->formFactory->create(
            $analyticsTopType,
            [
                'top_type' => $topType,
                'top_number' => 30,
                'range' => $this->analyticsManager->getDefaultRange(),
            ]
        );

        $criteriaForm->handleRequest($request);

        $range = $criteriaForm->get('range')->getData();
        $topType = $criteriaForm->get('top_type')->getData();
        $max = $criteriaForm->get('top_number')->getData();
        $listData = $this->analyticsManager->getTopByCriteria($range, $topType, $max);

        return [
            'form_criteria' => $criteriaForm->createView(),
            'list_data' => $listData,
        ];
    }

    /**
     * @EXT\Route(
     *     "/item/{item}",
     *     name="claro_admin_analytics_other_details"
     * )
     */
    public function analyticsItemAction($item)
    {
        /** @var \Claroline\CoreBundle\Event\Analytics\PlatformContentItemDetailsEvent $event */
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'administration_analytics_platform_content_item_details_'.$item,
            'Analytics\PlatformContentItemDetails'
        );

        return new Response($event->getContent());
    }
}
