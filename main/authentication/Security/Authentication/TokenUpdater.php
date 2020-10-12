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
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Allows to modify current user token at runtime.
 * This is mostly used to implement the ViewAs feature.
 */
class TokenUpdater
{
    /** @var string */
    private $secret;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;

    public function __construct(string $secret, TokenStorageInterface $tokenStorage, ObjectManager $om)
    {
        $this->secret = $secret;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    public function createAnonymous()
    {
        $token = new AnonymousToken($this->secret, 'anon.', ['ROLE_ANONYMOUS']);
        $this->tokenStorage->setToken($token);
    }

    public function cancelUserUsurpation(TokenInterface $token)
    {
        if ($token instanceof SwitchUserToken) {
            $user = $token->getOriginalToken()->getUser();
            $this->om->refresh($user);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
        }
    }

    public function cancelUsurpation(TokenInterface $token)
    {
        $user = $token->getUser();
        $this->om->refresh($user);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
