<?php

namespace Claroline\EvaluationBundle\Controller\Sequence;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Manager\SequenceEvaluationManager;
use Claroline\EvaluationBundle\Manager\SequenceManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/sequence', name: 'apiv2_evaluation_sequence_')]
class SequenceController extends AbstractCrudController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ToolManager $toolManager,
        private readonly SequenceManager $sequenceManager,
        private readonly SequenceEvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'evaluation_sequence';
    }

    public static function getClass(): string
    {
        return Sequence::class;
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'create' => [Options::PERSIST_TAG],
            'update' => [Options::PERSIST_TAG],
        ]);
    }

    #[Route(path: '/context/{context}/{contextId}', name: 'context_list', defaults: ['contextId' => null], methods: ['GET'])]
    public function listByContextAction(
        string $context,
        string $contextId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (WorkspaceContext::getName() === $context) {
            $finderQuery->addFilter('workspace', $contextId);
        }

        $roles = $this->tokenStorage->getToken()?->getRoleNames();
        if (!in_array(PlatformRoles::ADMIN, $roles) && !$this->toolManager->isGranted('FOLLOW', 'progression', $contextId)) {
            $finderQuery->addFilter('roles', $roles);
        }

        if (!$this->toolManager->isGranted('EDIT', 'progression', $contextId)) {
            $finderQuery->addFilter('published', true);
        }

        $sequences = $this->crud->search(Sequence::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $sequences->toResponse();
    }

    #[Route(path: '/resource/{resourceId}', name: 'resource_list', methods: ['GET'])]
    public function listByResourceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode
    ): JsonResponse {
        $this->checkPermission('OPEN', $resourceNode, [], true);

        return new JsonResponse();
    }

    #[Route(path: '/{id}', name: 'open', methods: ['GET'])]
    public function openAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Sequence $sequence
    ): JsonResponse {
        $this->checkPermission('OPEN', $sequence, [], true);

        $user = $this->tokenStorage->getToken()?->getUser();

        $this->sequenceManager->addView($sequence, $user);

        $evaluation = null;
        $progression = [];
        if ($user instanceof User) {
            $userEvaluation = $this->evaluationManager->getUserEvaluation($sequence, $user);

            $evaluation = $this->serializer->serialize($userEvaluation);
            $progression = $this->evaluationManager->getUserProgression($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return new JsonResponse([
            'sequence' => $this->serializer->serialize($sequence),
            'userEvaluation' => $evaluation,
            'progression' => $progression,
        ]);
    }

    #[Route(path: '/publish', name: 'publish', methods: ['PUT'])]
    public function publishAction(
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $data = $this->decodeRequest($request);

        $processed = [];

        $sequences = $this->om->getRepository(Sequence::class)->findBy(['uuid' => $data]);
        foreach ($sequences as $sequence) {
            if ($this->authorization->isGranted('EDIT', $sequence) && !$sequence->isPublished()) {
                $processed[] = $this->crud->update($sequence, [
                    'id' => $sequence->getUuid(),
                    'meta' => ['published' => true],
                ], [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION]);
            }
        }

        return new JsonResponse(array_map(function (Sequence $sequence) {
            return $this->serializer->serialize($sequence);
        }, $processed));
    }

    #[Route(path: '/unpublish', name: 'unpublish', methods: ['PUT'])]
    public function unpublishAction(
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $data = $this->decodeRequest($request);

        $processed = [];

        $sequences = $this->om->getRepository(Sequence::class)->findBy(['uuid' => $data]);
        foreach ($sequences as $sequence) {
            if ($this->authorization->isGranted('EDIT', $sequence) && $sequence->isPublished()) {
                $processed[] = $this->crud->update($sequence, [
                    'id' => $sequence->getUuid(),
                    'meta' => ['published' => false],
                ], [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION]);
            }
        }

        return new JsonResponse(array_map(function (Sequence $sequence) {
            return $this->serializer->serialize($sequence);
        }, $processed));
    }

    #[Route(path: '/copy/{workspaceId}', name: 'copy', methods: ['POST'])]
    public function copyAction(
        Request $request,
        #[MapEntity(mapping: ['workspaceId' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryParameter] ?bool $copyResources = false
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $sequenceIds = $this->decodeRequest($request);
        $toCopy = $this->om->getRepository(Sequence::class)->findBy(['uuid' => $sequenceIds]);

        $options = [Crud::NO_PERMISSIONS];
        if ($copyResources) {
            $options[] = 'copyResources';
        }

        foreach ($toCopy as $sequence) {
            if ($this->checkPermission('EDIT', $sequence)) {
                $this->crud->copy($sequence, $options, ['workspace' => $workspace]);
            }
        }

        return new JsonResponse(null, 204);
    }
}
