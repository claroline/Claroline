<?php

namespace Claroline\AppBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

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
}
