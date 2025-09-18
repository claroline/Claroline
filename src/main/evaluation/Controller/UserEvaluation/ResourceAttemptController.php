<?php

namespace Claroline\EvaluationBundle\Controller\UserEvaluation;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
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
 * Manages user attempts for resources {@see ResourceAttempt}.
 */
#[Route(path: '/resource_attempt')]
class ResourceAttemptController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns the list of user evaluations for a ResourceNode.
     */
    #[Route(path: '/{resourceId}', name: 'apiv2_resource_attempt_list', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['resourceId' => 'uuid'])]
        ResourceNode $resourceNode,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $finderQuery->addFilter('resourceNode', $resourceNode->getUuid());
        if (!$this->checkPermission('FOLLOW', $resourceNode)) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            $finderQuery->addFilter('user', $user->getUuid());
        }

        $evaluations = $this->crud->search(ResourceAttempt::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $evaluations->toResponse();
    }

    #[Route(path: '/id/{attemptId}', name: 'apiv2_resource_attempt_get', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['attemptId' => 'uuid'])]
        ResourceAttempt $resourceAttempt,
    ): JsonResponse {
        $this->checkPermission('OPEN', $resourceAttempt, [], true);

        return new JsonResponse([
            'evaluation' => $this->serializer->serialize($resourceAttempt),
        ]);
    }

    #[Route(path: '/', name: 'apiv2_resource_attempt_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint Crud will do it for us

        $attemptIds = $this->decodeRequest($request);
        $attempts = $this->om->getRepository(ResourceAttempt::class)->findBy([
            'uuid' => $attemptIds,
        ]);

        foreach ($attempts as $attempt) {
            $this->crud->delete($attempt);
        }

        return new JsonResponse(null, 204);
    }
}
