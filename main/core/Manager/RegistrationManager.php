<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/8/17
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class RegistrationManager.
 */
class RegistrationManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthenticationSuccessListener */
    private $authenticationHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationSuccessListener $authenticationHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationHandler = $authenticationHandler;
    }

    public function login(User $user)
    {
        //this is bad but I don't know any other way (yet)
        $providerKey = 'main';
        $token = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function loginUser($user, Request $request)
    {
        $token = $this->login($user);
        //a bit hacky I know ~
        return $this->authenticationHandler->onAuthenticationSuccess($request, $token);
    }
}
