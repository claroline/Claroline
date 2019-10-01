<?php

namespace Claroline\AnalyticsBundle\Controller\Resource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
 * @Route("/resource/{resourceId}/logs", requirements={"resourceId"="\d+"})
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
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/", name="apiv2_resource_logs_list")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     */
    public function listAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAcces($node);

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $this->getResourceNodeFilteredQuery($request, $node),
            []
        ));
    }

    /**
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/csv", name="apiv2_resource_logs_list_csv")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     */
    public function listCsvAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAcces($node);

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
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/chart", name="apiv2_resource_logs_list_chart")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     */
    public function listChartAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAcces($node);

        $chartData = $this->logManager->getChartData($this->getResourceNodeFilteredQuery($request, $node));

        return new JsonResponse($chartData);
    }

    /**
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/users", name="apiv2_resource_logs_list_users")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     */
    public function userActionsListAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAcces($node);
        $userList = $this->logManager->getUserActionsList($this->getResourceNodeFilteredQuery($request, $node));

        return new JsonResponse($userList);
    }

    /**
     * @param Request      $request
     * @param ResourceNode $node
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/users/csv", name="apiv2_resource_logs_list_users_csv")
     * @Method("GET")
     *
     * @ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resourceId": "id"}}
     * )
     */
    public function userActionsListCsvAction(Request $request, ResourceNode $node)
    {
        $this->checkLogsAcces($node);

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
     * @param Log $log
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/{id}", name="apiv2_resource_logs_get", requirements={"id"="\d+"})
     * @Method("GET")
     *
     * @ParamConverter("log", class="Claroline\CoreBundle\Entity\Log\Log", options={
     *     "mapping": {"resourceId": "resourceNode",
     *     "id": "id"
     * }})
     */
    public function getAction(Log $log)
    {
        $this->checkLogsAcces($log->getResourceNode());

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
    private function checkLogsAcces(ResourceNode $node)
    {
        if (!$this->authorizationChecker->isGranted('ADMINISTRATE', $node)) {
            throw new AccessDeniedHttpException();
        }
    }
}
