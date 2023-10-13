<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WorkspaceContext extends AbstractContext
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly WorkspaceManager $manager,
        private readonly WorkspaceRestrictionsManager $restrictionsManager,
        private readonly WorkspaceEvaluationManager $evaluationManager
    ) {
    }

    public static function getShortName(): string
    {
        return 'workspace';
    }

    public function getObject(?string $contextId): ?Workspace
    {
        // we receive the slug on context open,
        // and we receive the uuid when tools are opened
        return $this->om->getObject(['id' => $contextId, 'slug' => $contextId], Workspace::class, ['slug']);
    }

    public function getAdditionalData(?string $contextId): array
    {
        $workspace = $this->getObject($contextId);
        $user = $this->tokenStorage->getToken()->getUser();

        $userEvaluation = null;
        if ($user instanceof User) {
            $userEvaluation = $this->serializer->serialize(
                $this->evaluationManager->getUserEvaluation($workspace, $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            );
        }

        $rootNode = $this->om->getRepository(ResourceNode::class)->findOneBy(['workspace' => $workspace, 'parent' => null]);

        return [
            'userEvaluation' => $userEvaluation,
            // do not expose root resource here (used in the WS to configure opening target)
            'root' => $this->serializer->serialize($rootNode, [SerializerInterface::SERIALIZE_MINIMAL]),
        ];
    }

    public function isAvailable(?string $contextId, TokenInterface $token): bool
    {
        return true;
    }

    public function isManager(?string $contextId, TokenInterface $token): bool
    {
        $workspace = $this->getObject($contextId);

        return $this->manager->isManager($workspace, $token);
    }

    public function getAccessErrors(?string $contextId, TokenInterface $token): array
    {
        $workspace = $this->getObject($contextId);

        return $this->restrictionsManager->getErrors($workspace, $token->getUser() instanceof User ? $token->getUser() : null);
    }

    public function isImpersonated(?string $contextId, TokenInterface $token): bool
    {
        return $this->manager->isImpersonated($token);
    }

    public function getRoles(?string $contextId, TokenInterface $token): array
    {
        if (empty($contextId)) {
            return [];
        }

        $workspace = $this->getObject($contextId);

        return $this->manager->getTokenRoles($token, $workspace);
    }

    public function getTools(?string $contextId): array
    {
        // TODO : filter based on workspace model flag.
        return parent::getTools($contextId);
    }

    public function getShortcuts(?string $contextId): array
    {
        if (empty($contextId)) {
            return [];
        }

        $workspace = $this->getObject($contextId);

        // TODO : only export current user shortcuts (we get all roles for the configuration in community/editor)
        // $this->manager->getShortcuts($workspace, $this->tokenStorage->getToken()->getRoleNames()),

        return $workspace->getShortcuts()->toArray();
    }
}
