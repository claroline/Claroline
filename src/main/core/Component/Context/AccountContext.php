<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AccountContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager
    ) {
    }

    public static function getName(): string
    {
        return 'account';
    }

    public static function getIcon(): string
    {
        return 'user';
    }

    public static function getOrder(): int
    {
        return 4;
    }

    public function getObject(?string $contextId): ?User
    {
        return $this->securityManager->getCurrentUser();
    }

    public function isAvailable(): bool
    {
        return !empty($this->securityManager->getCurrentUser());
    }

    public function getAccessErrors(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }

    public function isManager(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return !empty($this->securityManager->getCurrentUser());
    }

    public function isImpersonated(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        $currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM === $role->getType();
        });
    }
}
