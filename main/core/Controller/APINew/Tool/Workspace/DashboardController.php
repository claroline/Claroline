<?php

namespace Claroline\CoreBundle\Controller\APINew\Tool\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/tools/workspace/{workspaceId}/dashboard", requirements={"workspaceId"="\d+"})
 */
class DashboardController
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var AnalyticsManager */
    private $analyticsManager;

    /**
     * @DI\InjectParams({
     *     "authorizationChecker"   = @DI\Inject("security.authorization_checker"),
     *     "analyticsManager"       = @DI\Inject("claroline.manager.analytics_manager")
     * })
     *
     * LogController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AnalyticsManager $analyticsManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->analyticsManager = $analyticsManager;
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/", name="apiv2_workspace_tool_dashboard")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspaceId": "id"}}
     * )
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
        if (!$this->authorizationChecker->isGranted('analytics', $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }
}
