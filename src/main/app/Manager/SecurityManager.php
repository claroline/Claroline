<?php

namespace Claroline\AppBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

/**
 * to move elsewhere.
 */
class SecurityManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function getCurrentUser(): ?User
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($currentUser instanceof User) {
            return $currentUser;
        }

        return null;
    }

    public function isImpersonated(): bool
    {
        return $this->tokenStorage->getToken() instanceof SwitchUserToken;
    }

    public function isAnonymous(): bool
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();

            return is_string($user);
        }

        return true;
    }

    public function isAdmin(): bool
    {
        return in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames());
    }
}
