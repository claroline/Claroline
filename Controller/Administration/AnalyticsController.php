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

use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AnalyticsController extends Controller
{
    private $userManager;
    private $toolManager;
    private $workspaceManager;
    private $formFactory;
    private $analyticsManager;
    private $request;
    private $analyticsTool;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "analyticsManager"    = @DI\Inject("claroline.manager.analytics_manager"),
     *     "request"             = @DI\Inject("request"),
     *     "sc"                  = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        UserManager $userManager,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        FormFactory $formFactory,
        AnalyticsManager $analyticsManager,
        Request $request,
        SecurityContextInterface $sc
    )
    {
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->formFactory = $formFactory;
        $this->analyticsManager = $analyticsManager;
        $this->request = $request;
        $this->toolManager = $toolManager;
        $this->sc = $sc;
        $this->analyticsTool = $toolManager->getAdminToolByName('platform_analytics');
    }

    /**
     * @EXT\Route(
     *     "/analytics/",
     *     name="claro_admin_analytics_show"
     * )
     *
     * @EXT\Method("GET")
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
        $this->checkOpen();

        $lastMonthActions = $this->analyticsManager->getDailyActionNumberForDateRange();
        $mostViewedWS = $this->analyticsManager->topWSByAction(null, 'ws_tool_read', 5);
        $mostViewedMedia = $this->analyticsManager->topMediaByAction(null, 'resource_read', 5);
        $mostDownloadedResources = $this->analyticsManager->topResourcesByAction(null, 'resource_export', 5);
        $usersCount = $this->userManager->countUsersForPlatformRoles();

        return array(
            'barChartData' => $lastMonthActions,
            'usersCount' => $usersCount,
            'mostViewedWS' => $mostViewedWS,
            'mostViewedMedia' => $mostViewedMedia,
            'mostDownloadedResources' => $mostDownloadedResources
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/connections",
     *     name="claro_admin_analytics_connections"
     * )
     *
     * @EXT\Method({"GET", "POST"})
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
        $this->checkOpen();

        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_CONNECTIONS,
            array(),
            array(
                "range" => $this->analyticsManager->getDefaultRange(),
                "unique" => "false"
            )
        );

        $criteriaForm->handleRequest($this->request);
        $unique = false;
        $range = null;

        if ($criteriaForm->isValid()) {
            $range = $criteriaForm->get('range')->getData();
            $unique = $criteriaForm->get('unique')->getData() === 'true';
        }

        $actionsForRange = $this->analyticsManager
            ->getDailyActionNumberForDateRange($range, 'user_login', $unique);

        $connections = $actionsForRange;
        $activeUsers = $this->analyticsManager->getActiveUsers();

        return array(
            'connections' => $connections,
            'form_criteria' => $criteriaForm->createView(),
            'activeUsers' => $activeUsers
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/resources",
     *     name="claro_admin_analytics_resources"
     * )
     *
     * @EXT\Method("GET")
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
        $this->checkOpen();

        $manager = $this->get('doctrine.orm.entity_manager');
        $wsCount = $this->workspaceManager->getNbWorkspaces();
        $resourceCount = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->countResourcesByType();

        return array(
            'wsCount' => $wsCount,
            'resourceCount' => $resourceCount
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/top/{topType}",
     *     name="claro_admin_analytics_top",
     *     defaults={"topType" = "top_users_connections"}
     * )
     *
     * @EXT\Method({"GET", "POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\Analytics:analytics_top.html.twig")
     *
     * Displays platform analytics top activity page
     *
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
        $this->checkOpen();

        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_TOP,
            array(),
            array(
                "top_type" => $topType,
                "top_number" => 30,
                "range" => $this->analyticsManager->getDefaultRange()
            )
        );

        $criteriaForm->handleRequest($request);

        $range = $criteriaForm->get('range')->getData();
        $topType = $criteriaForm->get('top_type')->getData();
        $max = $criteriaForm->get('top_number')->getData();
        $listData = $this->analyticsManager->getTopByCriteria($range, $topType, $max);

        return array(
            'form_criteria' => $criteriaForm->createView(),
            'list_data' => $listData
        );
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->analyticsTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
} 