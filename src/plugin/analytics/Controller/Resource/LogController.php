<?php

namespace Claroline\AnalyticsBundle\Controller\Resource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\LogManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resource/{resourceId}/logs")
 * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resourceId": "uuid"}})
 */
class LogController
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;
    /** @var FinderProvider */
    private $finder;
    /** @var LogManager */
    private $logManager;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FinderProvider $finder,
        LogManager $logManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->finder = $finder;
        $this->logManager = $logManager;
    }

    /**
     * @Route("/", name="apiv2_resource_logs_list", methods={"GET"})
     */
    public function listAction(Request $request, ResourceNode $resourceNode): JsonResponse
    {
        $this->checkLogsAccess($resourceNode);

        return new JsonResponse($this->finder->search(
            Log::class,
            $this->getResourceNodeFilteredQuery($request, $resourceNode)
        ));
    }

    /**
     * @Route("/csv", name="apiv2_resource_logs_list_csv", methods={"GET"})
     */
    public function listCsvAction(Request $request, ResourceNode $resourceNode): StreamedResponse
    {
        $this->checkLogsAccess($resourceNode);

        // Filter data, but return all of them
        $query = $this->getResourceNodeFilteredQuery($request, $resourceNode);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportLogsToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @Route("/users/csv", name="apiv2_resource_logs_list_users_csv", methods={"GET"})
     */
    public function userActionsListCsvAction(Request $request, ResourceNode $resourceNode): StreamedResponse
    {
        $this->checkLogsAccess($resourceNode);

        // Filter data, but return all of them
        $query = $this->getResourceNodeFilteredQuery($request, $resourceNode);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportUserActionToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="user_actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * Add resource node filter to request.
     */
    private function getResourceNodeFilteredQuery(Request $request, ResourceNode $node): array
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, ['resourceNode' => $node]);

        return $query;
    }

    /**
     * Checks user rights to access logs tool.
     */
    private function checkLogsAccess(ResourceNode $node)
    {
        if (!$this->authorizationChecker->isGranted('ADMINISTRATE', $node)) {
            throw new AccessDeniedException();
        }
    }
}
