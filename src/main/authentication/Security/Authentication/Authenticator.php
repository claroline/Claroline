<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Allows to manually manage user authentication and token.
 */
class Authenticator
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly AuthenticationSuccessListener $authenticationHandler
    ) {
    }

    /**
     * Checks if a user is the one stored in the token.
     */
    public function isAuthenticatedUser(User $user): bool
    {
        $currentUser = $this->tokenStorage->getToken()?->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            return true;
        }

        return false;
    }

    public function login(User $user, Request $request): Response
    {
        $token = $this->createToken($user);

        // manually call authentication success listener
        return $this->authenticationHandler->onAuthenticationSuccess($request, $token);
    }

    public function createAdminToken(User $user): TokenInterface
    {
        $token = new UsernamePasswordToken($user,'main', [PlatformRoles::ADMIN]);
        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function createToken(UserInterface $user, array $customRoles = []): TokenInterface
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), !empty($customRoles) ? $customRoles : $user->getRoles());
        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function cancelUserUsurpation(TokenInterface $token): TokenInterface
    {
        if ($token instanceof SwitchUserToken) {
            $user = $token->getOriginalToken()->getUser();
            $this->om->refresh($user);

            return $this->createToken($user);
        }

        return $token;
    }

    public function cancelUsurpation(TokenInterface $token): TokenInterface
    {
        $user = $token->getUser();
        $this->om->refresh($user);

        return $this->createToken($user);
    }
}
