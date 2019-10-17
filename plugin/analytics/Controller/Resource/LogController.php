<?php

namespace Claroline\AnalyticsBundle\Controller\Resource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\LogManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route("/resource/{resourceId}/logs", requirements={"resourceId"="\d+"})
 */
class LogController
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var LogManager */
    private $logManager;

    /**
     * LogController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     * @param LogManager                    $logManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FinderProvider $finder,
        SerializerProvider $serializer,
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
     * @EXT\Route("/", name="apiv2_resource_logs_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAccess($node);

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $this->getResourceNodeFilteredQuery($request, $node),
            []
        ));
    }

    /**
     * @EXT\Route("/csv", name="apiv2_resource_logs_list_csv")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return StreamedResponse
     */
    public function listCsvAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAccess($node);

        // Filter data, but return all of them
        $query = $this->getResourceNodeFilteredQuery($request, $node);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportLogsToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @EXT\Route("/chart", name="apiv2_resource_logs_list_chart")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return JsonResponse
     */
    public function listChartAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAccess($node);

        $chartData = $this->logManager->getChartData($this->getResourceNodeFilteredQuery($request, $node));

        return new JsonResponse($chartData);
    }

    /**
     * @EXT\Route("/users", name="apiv2_resource_logs_list_users")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return JsonResponse
     */
    public function userActionsListAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAccess($node);
        $userList = $this->logManager->getUserActionsList($this->getResourceNodeFilteredQuery($request, $node));

        return new JsonResponse($userList);
    }

    /**
     * @EXT\Route("/users/csv", name="apiv2_resource_logs_list_users_csv")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return StreamedResponse
     */
    public function userActionsListCsvAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAccess($node);

        // Filter data, but return all of them
        $query = $this->getResourceNodeFilteredQuery($request, $node);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportUserActionToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="user_actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @EXT\Route("/{id}", name="apiv2_resource_logs_get", requirements={"id"="\d+"})
     * @EXT\Method("GET")
     * @EXT\ParamConverter("log", class="Claroline\CoreBundle\Entity\Log\Log", options={
     *     "mapping": {"resourceId": "resourceNode",
     *     "id": "id"
     * }})
     *
     * @param Log $log
     *
     * @return JsonResponse
     */
    public function getAction(Log $log)
    {
        $this->checkLogsAccess($log->getResourceNode());

        return new JsonResponse($this->serializer->serialize($log, ['details' => true]));
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Log\Log';
    }

    /**
     * Add resource node filter to request.
     *
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return array
     */
    private function getResourceNodeFilteredQuery(Request $request, ResourceNode $node)
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, ['resourceNode' => $node]);

        return $query;
    }

    /**
     * Checks user rights to access logs tool.
     *
     * @param ResourceNode $node
     */
    private function checkLogsAccess(ResourceNode $node)
    {
        if (!$this->authorizationChecker->isGranted('ADMINISTRATE', $node)) {
            throw new AccessDeniedHttpException();
        }
    }
}
