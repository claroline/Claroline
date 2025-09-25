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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Manager\ExportManager;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manages user evaluations for resources {@see ResourceUserEvaluation}.
 */
#[Route(path: '/resource_evaluation')]
class ResourceEvaluationController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ToolManager $toolManager,
        private readonly ResourceEvaluationManager $evaluationManager,
        private readonly Crud $crud,
        private readonly ExportManager $exportManager,
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns the list of user evaluations for a ResourceNode.
     */
    #[Route(path: '/{parentType}/{parentId}', name: 'apiv2_resource_evaluation_list', requirements: ['parentType' => '(workspace|sequence|resource)?'], methods: ['GET'])]
    public function listAction(
        ?string $parentType = null,
        ?string $parentId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        switch ($parentType) {
            case 'resource':
                $resourceNode = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $parentId]);
                $manager = $this->checkPermission('FOLLOW', $resourceNode);

                $finderQuery->addFilter('resourceNode', $resourceNode->getUuid());
                break;

            case 'sequence':
                $sequence = $this->om->getRepository(Sequence::class)->findOneBy(['uuid' => $parentId]);
                $manager = $this->checkPermission('FOLLOW', $sequence);

                $finderQuery->addFilter('sequence', $sequence->getUuid());
                break;

            case 'workspace':
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $parentId]);
                $progressionTool = $this->toolManager->getOrderedTool('progression', WorkspaceContext::getName(), $parentId);
                $manager = $this->checkPermission('FOLLOW', $progressionTool);

                $finderQuery->addFilter('resourceNode.workspace', $workspace->getUuid());
                break;

            default:
                $progressionTool = $this->toolManager->getOrderedTool('progression', DesktopContext::getName());
                $manager = $this->checkPermission('FOLLOW', $progressionTool);
                break;
        }

        if (!$manager) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            $finderQuery->addFilter('user', $user->getUuid());
        }

        $evaluations = $this->crud->search(ResourceEvaluation::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $evaluations->toResponse();
    }

    #[Route(path: '/{resourceId}/csv', name: 'apiv2_resource_evaluation_csv', methods: ['GET'])]
    public function exportCsvAction(
        #[MapEntity(mapping: ['resourceId' => 'uuid'])]
        ResourceNode $resourceNode
    ): StreamedResponse {
        $this->checkPermission('FOLLOW', $resourceNode, [], true);

        return new StreamedResponse(function () use ($resourceNode): void {
            $this->exportManager->exportResourceEvaluations($resourceNode);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toFilename($resourceNode->getName()).'.csv',
        ]);
    }

    #[Route(path: '/{evaluationId}', name: 'apiv2_resource_evaluation_get', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['evaluationId' => 'uuid'])]
        ResourceEvaluation $resourceEvaluation
    ): JsonResponse {
        $this->checkPermission('OPEN', $resourceEvaluation, [], true);

        $attempts = [];
        if ($this->evaluationManager->supportsAttempts($resourceEvaluation->getResourceNode())) {
            $attempts = $this->om->getRepository(ResourceAttempt::class)->findBy([
                'resourceUserEvaluation' => $resourceEvaluation,
            ]);
        }

        $archives = [];
        if (!$resourceEvaluation->isArchived()) {
            $archives = $this->om->getRepository(ResourceEvaluation::class)->findBy([
                'user' => $resourceEvaluation->getUser(),
                'resourceNode' => $resourceEvaluation->getResourceNode(),
                'archived' => true,
            ]);
        }

        return new JsonResponse([
            'evaluation' => $this->serializer->serialize($resourceEvaluation),
            'progression' => array_map(function (ResourceAttempt $attempt) {
                return $this->serializer->serialize($attempt);
            }, $attempts),
            'archives' => array_map(function (ResourceEvaluation $archivedEvaluation) {
                return $this->serializer->serialize($archivedEvaluation);
            }, $archives),
        ]);
    }

    #[Route(path: '/', name: 'apiv2_resource_evaluation_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint Crud will do it for us

        $evaluationIds = $this->decodeRequest($request);
        $evaluations = $this->om->getRepository(ResourceEvaluation::class)->findBy([
            'uuid' => $evaluationIds,
        ]);

        foreach ($evaluations as $evaluation) {
            $this->crud->delete($evaluation);
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/attempts', name: 'apiv2_resource_evaluation_give_attempt', methods: ['PUT'])]
    public function giveAnotherAttemptAction(
        Request $request
    ): JsonResponse {
        $evaluationIds = $this->decodeRequest($request);
        $evaluations = $this->om->getRepository(ResourceEvaluation::class)->findBy([
            'uuid' => $evaluationIds,
        ]);

        foreach ($evaluations as $evaluation) {
            if ($this->checkPermission('ADMINISTRATE', $evaluation)) {
                $this->evaluationManager->giveAnotherAttempt($evaluation);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Recalculates (score, status, progression, ...) evaluations for all the users of a resource.
     */
    #[Route(path: '/{resourceId}/recompute', name: 'apiv2_resource_evaluation_recompute', methods: ['PUT'])]
    public function recomputeAction(
        #[MapEntity(mapping: ['resourceId' => 'uuid'])]
        ResourceNode $resourceNode
    ): JsonResponse {
        $this->checkPermission('EDIT', $resourceNode, [], true);

        $this->evaluationManager->recomputeEvaluations($resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Removes all user evaluations for the sequence.
     */
    #[Route(path: '/{resourceId}', name: 'apiv2_resource_evaluation_purge', methods: ['DELETE'])]
    public function purgeAction(
        #[MapEntity(mapping: ['resourceId' => 'uuid'])]
        ResourceNode $resourceNode
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $resourceNode, [], true);

        $this->evaluationManager->purgeEvaluations($resourceNode);

        return new JsonResponse(null, 204);
    }
}
