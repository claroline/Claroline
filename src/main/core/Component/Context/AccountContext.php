<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
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

    public static function getShortName(): string
    {
        return 'account';
    }

    public function getObject(?string $contextId): ?User
    {
        return $this->securityManager->getCurrentUser();
    }

    public function isAvailable(?string $contextId, TokenInterface $token): bool
    {
        return !empty($this->securityManager->getCurrentUser());
    }

    public function getAccessErrors(?string $contextId, TokenInterface $token): array
    {
        return [];
    }

    public function isManager(?string $contextId, TokenInterface $token): bool
    {
        return $this->securityManager->isAdmin();
    }

    public function isImpersonated(?string $contextId, TokenInterface $token): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function getAdditionalData(?string $contextId): array
    {
        return [];
    }

    public function getRoles(?string $contextId, TokenInterface $token): array
    {
        $currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM_ROLE === $role->getType();
        });
    }

    public function getShortcuts(?string $contextId): array
    {
        // only supported by Workspace context atm
        return [];
    }
}
