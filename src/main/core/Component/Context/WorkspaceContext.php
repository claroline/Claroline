<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\ContextComponent;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class WorkspaceContext extends ContextComponent
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SecurityManager $securityManager,
        private readonly WorkspaceManager $manager,
        private readonly WorkspaceRestrictionsManager $restrictionsManager
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

    public static function getSubjectClass(): string
    {
        return Workspace::class;
    }

    public function getSubject(?string $contextId): ?Workspace
    {
        if (empty($contextId)) {
            throw new \RuntimeException('WorkspaceContext can not be opened without an ID.');
        }

        // we receive the slug on context open,
        // and we receive the uuid when tools are opened
        $workspace = $this->om->getRepository(Workspace::class)->findByUuidOrSlug($contextId);
        if (empty($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }

        return $workspace;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function isGranted(string $permission, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->authorization->isGranted($permission, $contextSubject);
    }

    public function getAccessError(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): ?array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        return $this->restrictionsManager->getError($workspace, $token && $token->getUser() instanceof User ? $token->getUser() : null);
    }

    public function isImpersonated(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->manager->isImpersonated($token);
    }

    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        return $this->manager->getTokenRoles($token, $workspace);
    }

    public function getOrganizations(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        if ($this->securityManager->isAdmin()) {
            return $contextSubject->getOrganizations()->toArray();
        }

        $userOrganizations = $token?->getUser()?->getOrganizations() ?? [];
        $workspaceOrganizations = $contextSubject->getOrganizations()->toArray();

        return array_intersect($workspaceOrganizations, $userOrganizations);
    }

    public function create(?ContextSubjectInterface $contextSubject, array $data): void
    {
        // create collaborator role
        // create manager role
    }
}
