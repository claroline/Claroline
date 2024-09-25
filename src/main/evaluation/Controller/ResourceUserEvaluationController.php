<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manages user evaluations for resources {@see ResourceUserEvaluation}}.
 */
#[Route(path: '/resource_evaluation')]
class ResourceUserEvaluationController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly FinderProvider $finder,
        private readonly ResourceEvaluationManager $evaluationManager,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns the list of user evaluations for a ResourceNode.
     */
    #[Route(path: '/{nodeId}', name: 'apiv2_resource_evaluation_list', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['nodeId' => 'uuid'])]
        ResourceNode $resourceNode,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $finderQuery->addFilter('resourceNode', $resourceNode->getUuid());
        if (!$this->checkPermission('ADMINISTRATE', $resourceNode)) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            $finderQuery->addFilter('user', $user->getUuid());
        }

        $evaluations = $this->crud->search(ResourceUserEvaluation::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $evaluations->toResponse();
    }

    #[Route(path: '/attempts/{userEvaluationId}', name: 'apiv2_resource_evaluation_list_attempts', methods: ['GET'])]
    public function listAttemptsAction(
        #[MapEntity(mapping: ['userEvaluationId' => 'id'])]
        ResourceUserEvaluation $userEvaluation,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $userEvaluation, [], true);

        return new JsonResponse(
            $this->finder->search(ResourceEvaluation::class, array_merge($request->query->all(), ['hiddenFilters' => [
                'resourceUserEvaluation' => $userEvaluation,
            ]]))
        );
    }

    #[Route(path: '/attempts/{userEvaluationId}', name: 'apiv2_resource_evaluation_give_attempt', methods: ['PUT'])]
    public function giveAnotherAttemptAction(
        #[MapEntity(mapping: ['userEvaluationId' => 'id'])]
        ResourceUserEvaluation $userEvaluation
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $userEvaluation, [], true);

        $this->evaluationManager->giveAnotherAttempt($userEvaluation);

        return new JsonResponse(
            $this->serializer->serialize($userEvaluation)
        );
    }
}
