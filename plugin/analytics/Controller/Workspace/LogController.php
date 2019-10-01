<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Controller\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\Log\LogSerializer;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\LogManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/tools/workspace/{workspaceId}/logs", requirements={"workspaceId"="\d+"})
 */
class LogController
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FinderProvider */
    private $finder;

    /** @var LogSerializer */
    private $serializer;

    /** @var LogManager */
    private $logManager;

    /**
     * LogController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FinderProvider                $finder
     * @param LogSerializer                 $serializer
     * @param LogManager                    $logManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FinderProvider $finder,
        LogSerializer $serializer,
        LogManager $logManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->logManager = $logManager;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'log';
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/", name="apiv2_workspace_tool_logs_list")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspaceId": "id"}}
     * )
     */
    public function listAction(Request $request, Workspace $workspace)
    {
        $this->checkLogToolAccess($workspace);

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $this->getWorkspaceFilteredQuery($request, $workspace),
            []
        ));
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/csv", name="apiv2_workspace_tool_logs_list_csv")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspaceId": "id"}}
     * )
     */
    public function listCsvAction(Request $request, Workspace $workspace)
    {
        $this->checkLogToolAccess($workspace);

        // Filter data, but return all of them
        $query = $this->getWorkspaceFilteredQuery($request, $workspace);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportLogsToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/chart", name="apiv2_workspace_tool_logs_list_chart")
     * @Method("GET")
     *
     * @ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
     */
    public function listChartAction(Request $request, Workspace $workspace)
    {
        $this->checkLogToolAccess($workspace);

        $chartData = $this->logManager->getChartData($this->getWorkspaceFilteredQuery($request, $workspace));

        return new JsonResponse($chartData);
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/users", name="apiv2_workspace_tool_logs_list_users")
     * @Method("GET")
     *
     * @ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
     */
    public function userActionsListAction(Request $request, Workspace $workspace)
    {
        $this->checkLogToolAccess($workspace);
        $userList = $this->logManager->getUserActionsList($this->getWorkspaceFilteredQuery($request, $workspace));

        return new JsonResponse($userList);
    }

    /**
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/users/csv", name="apiv2_workspace_tool_logs_list_users_csv")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspaceId": "id"}}
     * )
     */
    public function userActionsListCsvAction(Request $request, Workspace $workspace)
    {
        $this->checkLogToolAccess($workspace);

        // Filter data, but return all of them
        $query = $this->getWorkspaceFilteredQuery($request, $workspace);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportUserActionToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="user_actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @param Log $log
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/{id}", name="apiv2_workspace_tool_logs_get", requirements={"id"="\d+"})
     * @Method("GET")
     *
     * @ParamConverter("log", class="Claroline\CoreBundle\Entity\Log\Log", options={
     *     "mapping": {"workspaceId": "workspace",
     *     "id": "id"
     * }})
     */
    public function getAction(Log $log)
    {
        $this->checkLogToolAccess($log->getWorkspace());

        return new JsonResponse($this->serializer->serialize($log, ['details' => true]));
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Log\Log';
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

        return $query;
    }

    /**
     * Checks user rights to access logs tool.
     *
     * @param Workspace $workspace
     */
    private function checkLogToolAccess(Workspace $workspace)
    {
        if (!$this->authorizationChecker->isGranted('dashboard', $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }
}
