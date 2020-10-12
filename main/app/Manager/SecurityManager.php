<?php

namespace Claroline\AppBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Role\Role;

class SecurityManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function isImpersonated()
    {
        return $this->tokenStorage->getToken() instanceof SwitchUserToken;
    }

    public function isAnonymous()
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();

            return is_string($user);
        }

        return true;
    }

    /**
     * @deprecated use TokenStorageInterface::getRoles()->getRolenames()
     */
    public function getRoles()
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            return array_map(function (Role $role) {
                return $role->getRole();
            }, $token->getRoles());
        }

        return [];
    }
}
