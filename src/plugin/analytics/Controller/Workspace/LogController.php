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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace/{workspaceId}/logs")
 * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "uuid"}})
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
     * @Route("/", name="apiv2_workspace_tool_logs_list", methods={"GET"})
     */
    public function listAction(Request $request, Workspace $workspace): JsonResponse
    {
        $this->checkLogToolAccess($workspace);

        return new JsonResponse($this->finder->search(
            Log::class,
            $this->getWorkspaceFilteredQuery($request, $workspace)
        ));
    }

    /**
     * @Route("/csv", name="apiv2_workspace_tool_logs_list_csv", methods={"GET"})
     */
    public function listCsvAction(Request $request, Workspace $workspace): StreamedResponse
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
     * @Route("/users/csv", name="apiv2_workspace_tool_logs_list_users_csv", methods={"GET"})
     */
    public function userActionsListCsvAction(Request $request, Workspace $workspace): StreamedResponse
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
     * Add workspace filter to request.
     */
    private function getWorkspaceFilteredQuery(Request $request, Workspace $workspace): array
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, ['workspace' => $workspace]);

        return $query;
    }

    /**
     * Checks user rights to access logs tool.
     */
    private function checkLogToolAccess(Workspace $workspace)
    {
        if (!$this->authorizationChecker->isGranted('dashboard', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
