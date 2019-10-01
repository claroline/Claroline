<?php

namespace Claroline\AnalyticsBundle\Controller\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route("/tools/workspace/{workspaceId}/dashboard", requirements={"workspaceId"="\d+"})
 */
class DashboardController
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var AnalyticsManager */
    private $analyticsManager;

    /**
     * LogController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AnalyticsManager              $analyticsManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AnalyticsManager $analyticsManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->analyticsManager = $analyticsManager;
    }

    /**
     * @EXT\Route("/", name="apiv2_workspace_tool_dashboard")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspaceId": "id"}}
     * )
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, Workspace $workspace)
    {
        $this->checkDashboardToolAccess($workspace);

        $query = $this->getWorkspaceFilteredQuery($request, $workspace);
        $chartData = $this->analyticsManager->getDailyActions($query);
        $resourceTypes = $this->analyticsManager->getResourceTypesCount($workspace);

        return new JsonResponse([
            'activity' => $chartData,
            'resourceTypes' => $resourceTypes,
        ]);
    }

    /**
     * Add workspace filter to request.
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getWorkspaceFilteredQuery(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, ['workspace' => $workspace]);
        $filters = isset($query['filters']) ? $query['filters'] : [];
        $query['filters'] = array_merge($filters, ['action' => 'workspace-enter']);

        return $query;
    }

    /**
     * Checks user rights to access logs tool.
     *
     * @param Workspace $workspace
     */
    private function checkDashboardToolAccess(Workspace $workspace)
    {
        if (!$this->authorizationChecker->isGranted('dashboard', $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }
}
