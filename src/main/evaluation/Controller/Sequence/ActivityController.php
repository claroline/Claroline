<?php

namespace Claroline\EvaluationBundle\Controller\Sequence;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Manager\ViewerManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\SequenceView;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Finder\SequenceViewType;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/sequence/{id}')]
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

    #[Route(path: '/logs', name: 'apiv2_sequence_functional_logs', methods: ['GET'])]
    public function functionalLogsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        $finderQuery->addFilter('objectClass', Sequence::class);
        $finderQuery->addFilter('objectId', $sequence->getUuid());

        $logs = $this->crud->search(FunctionalLog::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/activity/{activityType<(views|visitors|actions)>}', name: 'apiv2_sequence_activity', methods: ['GET'])]
    public function activityAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
        string $activityType
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        switch ($activityType) {
            case 'views':
                $activity = $this->om->getRepository(SequenceView::class)->findViewsForPeriod(
                    $sequence,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );

                break;
            case 'visitors':
                $activity = $this->om->getRepository(SequenceView::class)->findVisitorsForPeriod(
                    $sequence,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;

            case 'actions':
                $activity = $this->om->getRepository(FunctionalLog::class)->findActionsForPeriod(
                    Sequence::class,
                    $sequence->getUuid(),
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;
        }

        return new JsonResponse($activity);
    }

    #[Route(path: '/views', name: 'apiv2_sequence_views', methods: ['GET'])]
    public function viewsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        $finderQuery->addFilter('sequence', $sequence->getUuid());

        $viewers = $this->viewerManager->listViews(SequenceViewType::class, $finderQuery);

        return $viewers->toResponse();
    }

    #[Route(path: '/metrics', name: 'apiv2_sequence_metrics', methods: ['GET'])]
    public function metricsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        return new JsonResponse([
            'views' => [
                'count' => $sequence->getViews(),
                'visitors' => $this->viewerManager->countVisitors(
                    SequenceView::class,
                    $sequence,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                ),
            ],
            'completion' => $this->om->getRepository(SequenceEvaluation::class)->findCompletion(
                $sequence,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
            'success' => $this->om->getRepository(SequenceEvaluation::class)->findSuccess(
                $sequence,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
        ]);
    }

    #[Route(path: '/evaluation_status', name: 'apiv2_sequence_evaluation_status', methods: ['GET'])]
    public function statusesAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        return new JsonResponse(
            $this->om->getRepository(SequenceEvaluation::class)->countByStatus(
                $sequence,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            )
        );
    }

    #[Route(path: '/evaluation_completion', name: 'apiv2_sequence_evaluation_completion', methods: ['GET'])]
    public function statsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        return new JsonResponse($this->om->getRepository(SequenceEvaluation::class)->findCompletionStats(
            $sequence,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }

    #[Route(path: '/evaluation_scores', name: 'apiv2_sequence_evaluation_scores', methods: ['GET'])]
    public function scoresAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence,
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        if (!$sequence->getScoreTotal()) {
            throw new NotFoundHttpException('This sequence does not have any scores.');
        }

        return new JsonResponse($this->om->getRepository(SequenceEvaluation::class)->findScoreStats(
            $sequence,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }
}
