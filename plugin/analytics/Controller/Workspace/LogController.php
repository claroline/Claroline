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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/tools/workspace/{workspaceId}/logs")
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

    public function getClass()
    {
        return Log::class;
    }

    /**
     * @EXT\Route("/", name="apiv2_workspace_tool_logs_list")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "uuid"}})
     * @EXT\Method("GET")
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
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
     * @EXT\Route("/csv", name="apiv2_workspace_tool_logs_list_csv")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "uuid"}})
     * @EXT\Method("GET")
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return StreamedResponse
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
     * @EXT\Route("/users/csv", name="apiv2_workspace_tool_logs_list_users_csv")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspaceId": "uuid"}})
     * @EXT\Method("GET")
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return StreamedResponse
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
            throw new AccessDeniedException();
        }
    }
}
