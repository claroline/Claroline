<?php

namespace Claroline\AnalyticsBundle\Controller\Resource;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\LogManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resource/dashboard")
 */
class DashboardController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;
    /** @var AnalyticsManager */
    private $analyticsManager;
    /** @var EventManager */
    private $eventManager;
    /** @var LogManager */
    private $logManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        AnalyticsManager $analyticsManager,
        EventManager $eventManager,
        LogManager $logManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->analyticsManager = $analyticsManager;
        $this->eventManager = $eventManager;
        $this->logManager = $logManager;
    }

    /**
     * @Route("/{resource}/activity", name="apiv2_resource_analytics_activity", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resource": "uuid"}})
     */
    public function activityAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        $this->checkDashboardAccess($resourceNode);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'resourceNode' => $resourceNode,
        ];

        return new JsonResponse([
            'actions' => $this->analyticsManager->getDailyActions($query),
            'visitors' => $this->analyticsManager->getDailyActions(array_merge_recursive($query, [
                'hiddenFilters' => [
                    'action' => LogResourceReadEvent::ACTION,
                    'unique' => true,
                ],
            ])),
        ]);
    }

    /**
     * @Route("/{resource}/actions", name="apiv2_resource_analytics_actions", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resource": "uuid"}})
     */
    public function actionsAction(Request $request, ResourceNode $resourceNode)
    {
        $this->checkDashboardAccess($resourceNode);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'resourceNode' => $resourceNode,
        ];

        $chartData = $this->logManager->getChartData($query);

        return new JsonResponse([
            'types' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
            'actions' => $chartData,
        ]);
    }

    /**
     * @Route("/{resource}/time", name="apiv2_resource_analytics_time", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resource": "uuid"}})
     */
    public function connectionTimeAction(ResourceNode $resourceNode): JsonResponse
    {
        $this->checkDashboardAccess($resourceNode);

        return new JsonResponse([
            'total' => [],
            'average' => [],
        ]);
    }

    /**
     * @Route("/{resource}/users", name="apiv2_resource_analytics_users", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resource": "uuid"}})
     */
    public function usersAction(ResourceNode $resourceNode): JsonResponse
    {
        $this->checkDashboardAccess($resourceNode);

        return new JsonResponse(
            $this->analyticsManager->userRolesData($resourceNode)
        );
    }

    /**
     * @Route("/{resource}/users/top", name="apiv2_resource_analytics_top_users", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"resource": "uuid"}})
     */
    public function topUsersAction(ResourceNode $resourceNode): JsonResponse
    {
        $this->checkDashboardAccess($resourceNode);

        $options = [
            'page' => 0,
            'limit' => 10,
            'sortBy' => '-created',
            'hiddenFilters' => [
                'workspace' => $resourceNode->getUuid(),
            ],
        ];

        return new JsonResponse(
            $this->finder->search(User::class, $options)['data']
        );
    }

    /**
     * Checks user rights to access logs tool.
     */
    private function checkDashboardAccess(ResourceNode $resourceNode)
    {
        if (!$this->authorization->isGranted('ADMINISTRATE', $resourceNode)) {
            throw new AccessDeniedException();
        }
    }
}
