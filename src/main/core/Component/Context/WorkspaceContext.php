<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public static function getName(): string
    {
        return 'workspace';
    }

    public static function getIcon(): string
    {
        return 'book';
    }

    public function getObject(?string $contextId): ?Workspace
    {
        if (empty($contextId)) {
            throw new \RuntimeException('WorkspaceContext can not be opened without an ID.');
        }

        // we receive the slug on context open,
        // and we receive the uuid when tools are opened
        $workspace = $this->om->getObject(['uuid' => $contextId, 'slug' => $contextId], Workspace::class, ['slug']);
        if (empty($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        return $workspace;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function isRoot(): bool
    {
        return false;
    }

    public function isManager(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        return $this->manager->isManager($workspace, $token);
    }

    public function getAccessErrors(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        return $this->restrictionsManager->getErrors($workspace, $token->getUser() instanceof User ? $token->getUser() : null);
    }

    public function isImpersonated(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->manager->isImpersonated($token);
    }

    public function getRoles(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        return $this->manager->getTokenRoles($token, $workspace);
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;
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

    public function getShortcuts(?ContextSubjectInterface $contextSubject): array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        // TODO : only export current user shortcuts (we get all roles for the configuration in community/editor)
        // $this->manager->getShortcuts($workspace, $this->tokenStorage->getToken()->getRoleNames()),

        return $workspace->getShortcuts()->toArray();
    }
}
