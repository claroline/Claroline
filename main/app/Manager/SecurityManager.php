<?php

namespace Claroline\AppBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class SecurityManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * SecurityManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function isImpersonated()
    {
        if ($token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isAnonymous()
    {
        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();

            return is_string($user);
        }

        return true;
    }

    public function getRoles()
    {
        if ($token = $this->tokenStorage->getToken()) {
            return array_map(function (Role $role) {
                return $role->getRole();
            }, $token->getRoles());
        }

        return [];
    }
}
