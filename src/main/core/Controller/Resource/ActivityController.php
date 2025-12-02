<?php

namespace Claroline\CoreBundle\Controller\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Manager\ViewerManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceView;
use Claroline\CoreBundle\Finder\ResourceViewType;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/resource/{id}')]
class ActivityController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly ViewerManager $viewerManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/logs', name: 'apiv2_resource_functional_logs', methods: ['GET'])]
    public function functionalLogsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        $finderRequest->addFilter('objectClass', ResourceNode::class);
        $finderRequest->addFilter('objectId', $resource->getUuid());

        $logs = $this->crud->search(FunctionalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/activity/{activityType<(views|visitors|actions)>}', name: 'apiv2_resource_activity', methods: ['GET'])]
    public function activityAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
        string $activityType
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        switch ($activityType) {
            case 'views':
                $activity = $this->om->getRepository(ResourceView::class)->findViewsForPeriod(
                    $resource,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );

                break;
            case 'visitors':
                $activity = $this->om->getRepository(ResourceView::class)->findVisitorsForPeriod(
                    $resource,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;

            case 'actions':
                $activity = $this->om->getRepository(FunctionalLog::class)->findActionsForPeriod(
                    ResourceNode::class,
                    $resource->getUuid(),
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;
        }

        return new JsonResponse($activity);
    }

    #[Route(path: '/views', name: 'apiv2_resource_views', methods: ['GET'])]
    public function viewsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        $finderRequest->addFilter('resource', $resource->getUuid());

        $viewers = $this->viewerManager->listViews(ResourceViewType::class, $finderRequest);

        return $viewers->toResponse();
    }

    #[Route(path: '/metrics', name: 'apiv2_resource_metrics', methods: ['GET'])]
    public function metricsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        return new JsonResponse([
            'views' => [
                'count' => $resource->getViews(),
                'visitors' => $this->viewerManager->countVisitors(
                    ResourceView::class,
                    $resource,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                ),
            ],
            'completion' => $this->om->getRepository(ResourceEvaluation::class)->findCompletion(
                $resource,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
            'success' => $this->om->getRepository(ResourceEvaluation::class)->findSuccess(
                $resource,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
        ]);
    }

    #[Route(path: '/evaluation_status', name: 'apiv2_resource_evaluation_status', methods: ['GET'])]
    public function statusesAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        return new JsonResponse(
            $this->om->getRepository(ResourceEvaluation::class)->countByStatus(
                $resource,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            )
        );
    }

    #[Route(path: '/evaluation_completion', name: 'apiv2_resource_evaluation_completion', methods: ['GET'])]
    public function statsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        return new JsonResponse($this->om->getRepository(ResourceEvaluation::class)->findCompletionStats(
            $resource,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }

    #[Route(path: '/evaluation_scores', name: 'apiv2_resource_evaluation_scores', methods: ['GET'])]
    public function scoresAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resource,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resource, [], true);

        /*if (!$resource->getScoreTotal()) {
            throw new NotFoundHttpException('This resource does not have any scores.');
        }*/

        return new JsonResponse($this->om->getRepository(ResourceEvaluation::class)->findScoreStats(
            $resource,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }
}
