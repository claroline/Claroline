<?php

namespace Claroline\EvaluationBundle\Controller\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Manager\ViewerManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceView;
use Claroline\CoreBundle\Finder\WorkspaceViewType;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/workspace_evaluation/{id}')]
class ActivityController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly ToolManager $toolManager,
        private readonly ViewerManager $viewerManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/logs', name: 'apiv2_workspace_functional_logs', methods: ['GET'])]
    public function functionalLogsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $finderQuery->addFilter('objectClass', Workspace::class);
        $finderQuery->addFilter('objectId', $workspace->getUuid());
        $finderQuery->addFilter('event', 'evaluation');

        $logs = $this->crud->search(FunctionalLog::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/activity/{activityType<(views|visitors|actions)>}', name: 'apiv2_workspace_activity', methods: ['GET'])]
    public function activityAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        string $activityType
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        switch ($activityType) {
            case 'views':
                $activity = $this->om->getRepository(WorkspaceView::class)->findViewsForPeriod(
                    $workspace,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );

                break;
            case 'visitors':
                $activity = $this->om->getRepository(WorkspaceView::class)->findVisitorsForPeriod(
                    $workspace,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;

            case 'actions':
                $activity = $this->om->getRepository(FunctionalLog::class)->findActionsForPeriod(
                    Workspace::class,
                    $workspace->getUuid(),
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;
        }

        return new JsonResponse($activity);
    }

    #[Route(path: '/views', name: 'apiv2_workspace_views', methods: ['GET'])]
    public function viewsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $finderQuery->addFilter('workspace', $workspace->getUuid());

        $viewers = $this->viewerManager->listViews(WorkspaceViewType::class, $finderQuery);

        return $viewers->toResponse();
    }

    #[Route(path: '/metrics', name: 'apiv2_workspace_metrics', methods: ['GET'])]
    public function metricsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        return new JsonResponse([
            'views' => [
                'count' => $workspace->getViews(),
                'visitors' => $this->viewerManager->countVisitors(
                    WorkspaceView::class,
                    $workspace,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                ),
            ],
            'completion' => $this->om->getRepository(WorkspaceEvaluation::class)->findCompletion(
                $workspace,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
            'success' => $this->om->getRepository(WorkspaceEvaluation::class)->findSuccess(
                $workspace,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            ),
        ]);
    }

    #[Route(path: '/evaluation_status', name: 'apiv2_workspace_evaluation_status', methods: ['GET'])]
    public function statusesAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        return new JsonResponse(
            $this->om->getRepository(WorkspaceEvaluation::class)->countByStatus(
                $workspace,
                $this->tokenStorage->getToken()->getUser()->getMainOrganization()
            )
        );
    }

    #[Route(path: '/evaluation_completion', name: 'apiv2_workspace_evaluation_completion', methods: ['GET'])]
    public function statsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        return new JsonResponse($this->om->getRepository(WorkspaceEvaluation::class)->findCompletionStats(
            $workspace,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }

    #[Route(path: '/evaluation_scores', name: 'apiv2_workspace_evaluation_scores', methods: ['GET'])]
    public function scoresAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        if (!$workspace->getScoreTotal()) {
            throw new NotFoundHttpException('This workspace does not have any scores.');
        }

        return new JsonResponse($this->om->getRepository(WorkspaceEvaluation::class)->findScoreStats(
            $workspace,
            $this->tokenStorage->getToken()->getUser()->getMainOrganization()
        ));
    }

    private function checkToolAccess(string $permission, ?string $contextId = null): void
    {
        if ($contextId) {
            $tool = $this->toolManager->getOrderedTool('progression', WorkspaceContext::getName(), $contextId);
        } else {
            $tool = $this->toolManager->getOrderedTool('progression', DesktopContext::getName());
        }

        if (is_null($tool) || !$this->authorization->isGranted($permission, $tool)) {
            throw new AccessDeniedException(sprintf('Operation "%s" cannot be done on object %s', $permission, get_class($tool)));
        }
    }
}
