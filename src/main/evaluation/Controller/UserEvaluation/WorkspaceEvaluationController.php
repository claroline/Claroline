<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller\UserEvaluation;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Certificate\WorkspaceCertificate;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Manager\ExportManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages user evaluations for workspaces {@see WorkspaceEvaluation}}.
 */
#[Route(path: '/workspace_evaluation')]
class WorkspaceEvaluationController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly WorkspaceEvaluationManager $manager,
        private readonly ExportManager $exportManager,
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{parentType}/{parentId}', name: 'apiv2_workspace_evaluation_list', requirements: ['parentType' => '(workspace)+', 'parentId' => '.+'], defaults: ['parentType' => null, 'parentId' => null], methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery(),
        ?string $parentId = null
    ): StreamedJsonResponse {
        $workspace = null;
        if ($parentId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $parentId]);
            if (empty($workspace)) {
                throw new NotFoundHttpException();
            }
        }

        $this->checkToolAccess('OPEN', $workspace);

        if (!$this->checkToolAccess('EDIT', $workspace)) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            $finderQuery->addFilter('user', $user->getUuid());
        }

        if ($workspace) {
            $finderQuery->addFilter('workspace', $workspace->getUuid());
        }

        $evaluations = $this->crud->search(WorkspaceEvaluation::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $evaluations->toResponse();
    }

    #[Route(path: '/{workspaceId}/csv', name: 'apiv2_workspace_evaluation_csv', methods: ['GET'])]
    public function exportCsvAction(
        #[MapEntity(mapping: ['workspaceId' => 'uuid'])]
        Workspace $workspace
    ): StreamedResponse {
        $this->checkToolAccess('FOLLOW', $workspace);

        return new StreamedResponse(function () use ($workspace): void {
            $this->exportManager->exportWorkspaceEvaluations($workspace);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toFilename($workspace->getName()).'.csv',
        ]);
    }

    #[Route(path: '/{evaluationId}', name: 'apiv2_workspace_evaluation_get', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['evaluationId' => 'uuid'])]
        WorkspaceEvaluation $workspaceEvaluation
    ): JsonResponse {
        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        $workspace = $workspaceEvaluation->getWorkspace();

        $certificates = [];
        if ($workspaceEvaluation->isCertified()) {
            $certificates = $this->om->getRepository(WorkspaceCertificate::class)->findBy([
                'evaluation' => $workspaceEvaluation,
            ], [
                'obtentionDate' => 'DESC',
                'issueDate' => 'DESC',
            ]);
        }

        $archives = [];
        if (!$workspaceEvaluation->isArchived()) {
            $archives = $this->om->getRepository(WorkspaceEvaluation::class)->findBy([
                'user' => $workspaceEvaluation->getUser(),
                'workspace' => $workspaceEvaluation->getWorkspace(),
                'archived' => true,
            ]);
        }

        return new JsonResponse([
            'parameters' => [
                'successCondition' => $workspace->getSuccessCondition(),
            ],
            'evaluation' => $this->serializer->serialize($workspaceEvaluation),
            'progression' => array_map(function (SequenceEvaluation $evaluation) {
                return $this->serializer->serialize($evaluation);
            }, $workspaceEvaluation->getSequenceEvaluations()->toArray()),
            'certificates' => array_map(function (WorkspaceCertificate $certificate) {
                return $this->serializer->serialize($certificate);
            }, $certificates),
            'archives' => array_map(function (WorkspaceEvaluation $archivedEvaluation) {
                return $this->serializer->serialize($archivedEvaluation);
            }, $archives),
        ]);
    }

    #[Route(path: '/', name: 'apiv2_workspace_evaluation_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint Crud will do it for us

        $evaluationIds = $this->decodeRequest($request);
        $evaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy([
            'uuid' => $evaluationIds,
        ]);

        foreach ($evaluations as $evaluation) {
            $this->crud->delete($evaluation);
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/restart', name: 'apiv2_workspace_evaluation_archive', methods: ['PUT'])]
    public function restartAction(Request $request): JsonResponse
    {
        $evaluationIds = $this->decodeRequest($request);
        $evaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy([
            'uuid' => $evaluationIds,
        ]);

        foreach ($evaluations as $evaluation) {
            if ($this->checkPermission('ADMINISTRATE', $evaluation)) {
                $this->manager->archiveEvaluation($evaluation);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Initializes evaluations for all the users of a workspace.
     */
    #[Route(path: '/{workspace}/init', name: 'apiv2_workspace_evaluation_init', methods: ['PUT'])]
    public function initializeAction(
        #[MapEntity(mapping: ['workspace' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace);

        $this->manager->initialize($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     * Recalculates (score, status, progression, ...) evaluations for all the users of a workspace.
     */
    #[Route(path: '/{workspaceId}/recompute', name: 'apiv2_workspace_evaluation_recompute', methods: ['PUT'])]
    public function recomputeAction(
        #[MapEntity(mapping: ['workspaceId' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $evaluationIds = $this->decodeRequest($request);

        // recompute all the sequence evaluations
        if (empty($evaluationIds)) {
            $this->checkToolAccess('FOLLOW', $workspace);
            $this->manager->recomputeEvaluations($workspace);

            return new JsonResponse(null, 204);
        }

        // recompute selected evaluations
        foreach ($evaluationIds as $evaluationId) {
            $evaluation = $this->om->getRepository(WorkspaceEvaluation::class)->findOneBy([
                'uuid' => $evaluationId,
            ]);

            if ($evaluation && $this->checkPermission('ADMINISTRATE', $evaluation)) {
                $this->manager->refreshEvaluation($evaluation);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Removes all user evaluations for the workspace.
     */
    #[Route(path: '/{workspaceId}', name: 'apiv2_workspace_evaluation_purge', methods: ['DELETE'])]
    public function purgeAction(
        #[MapEntity(mapping: ['workspaceId' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkToolAccess('ADMINISTRATE', $workspace);

        $this->manager->purgeEvaluations($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     * Checks user rights to access evaluation tool.
     */
    private function checkToolAccess(string $permission, ?Workspace $workspace = null): bool
    {
        if (!empty($workspace)) {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndContext('progression', WorkspaceContext::getName(), $workspace->getUuid());
        } else {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndContext('progression', DesktopContext::getName());
        }

        return $this->checkPermission($permission, $evaluationTool, [], true);
    }
}
